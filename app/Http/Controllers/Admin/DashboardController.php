<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Form, FormEntry, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // Filter opsional: department_id, form_id, date_from, date_to
        $filters = [
            'department_id' => $r->integer('department_id') ?: null,
            'form_id'       => $r->integer('form_id') ?: null,
            'date_from'     => $r->date('date_from') ?: null,
            'date_to'       => $r->date('date_to') ?: null,
        ];

        $departments = Department::orderBy('name')->get(['id','name']);
        $forms       = Form::orderBy('title')->get(['id','title']);

        return view('admin.dashboard.index', compact('filters','departments','forms'));
    }

    /** Kartu ringkas: total form, total entries, aktif form, pengguna unik */
    public function summary(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);

        $cacheKey = 'dash_summary:'.md5(json_encode([$from,$to,$deptId,$formId]));
        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            $formQuery = Form::query();
            $entryQuery = FormEntry::query();
            if ($deptId) { $formQuery->where('department_id', $deptId); $entryQuery->whereHas('form', fn($q)=>$q->where('department_id',$deptId)); }
            if ($formId) { $formQuery->where('id',$formId); $entryQuery->where('form_id',$formId); }
            if ($from)   { $entryQuery->where('created_at','>=',$from->startOfDay()); }
            if ($to)     { $entryQuery->where('created_at','<=',$to->endOfDay()); }

            $totalForms    = (clone $formQuery)->count();
            $activeForms   = (clone $formQuery)->where('is_active', true)->count();
            $totalEntries  = (clone $entryQuery)->count();
            $uniqueUsers   = (clone $entryQuery)->distinct('user_id')->count('user_id');

            return compact('totalForms','activeForms','totalEntries','uniqueUsers');
        });

        return response()->json($data);
    }

    /** Tren entries per hari (default 30 hari terakhir) */
    public function entriesByDay(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        if (!$from && !$to) {
            $to = now();
            $from = now()->copy()->subDays(29);
        }

        $cacheKey = 'dash_entries_by_day:'.md5(json_encode([$from,$to,$deptId,$formId]));
        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            $q = FormEntry::selectRaw('DATE(created_at) d, COUNT(*) c')
                ->when($deptId, fn($qq)=>$qq->whereHas('form', fn($f)=>$f->where('department_id',$deptId)))
                ->when($formId, fn($qq)=>$qq->where('form_id',$formId))
                ->when($from, fn($qq)=>$qq->where('created_at','>=',$from->startOfDay()))
                ->when($to,   fn($qq)=>$qq->where('created_at','<=',$to->endOfDay()))
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            // lengkapi tanggal kosong supaya chart mulus
            $map = $q->keyBy('d');
            $labels = [];
            $series = [];
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $key = $d->toDateString();
                $labels[] = $key;
                $series[] = (int)($map[$key]->c ?? 0);
            }
            return compact('labels','series');
        });

        return response()->json($data);
    }

    /** Top Forms berdasarkan jumlah entries pada rentang */
    public function topForms(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        $cacheKey = 'dash_top_forms:'.md5(json_encode([$from,$to,$deptId,$formId]));

        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            $rows = FormEntry::select('form_id', DB::raw('COUNT(*) as c'))
                ->when($deptId, fn($qq)=>$qq->whereHas('form', fn($f)=>$f->where('department_id',$deptId)))
                ->when($formId, fn($qq)=>$qq->where('form_id',$formId))
                ->when($from, fn($qq)=>$qq->where('created_at','>=',$from->startOfDay()))
                ->when($to,   fn($qq)=>$qq->where('created_at','<=',$to->endOfDay()))
                ->groupBy('form_id')
                ->orderByDesc('c')
                ->limit(10)
                ->get();

            $formTitles = Form::whereIn('id', $rows->pluck('form_id'))->pluck('title','id');
            $labels = $rows->map(fn($r)=>$formTitles[$r->form_id] ?? ('Form #'.$r->form_id));
            $series = $rows->pluck('c')->map(fn($v)=>(int)$v);
            return ['labels'=>$labels->values(), 'series'=>$series->values()];
        });

        return response()->json($data);
    }

    /** Rekap per-department (jumlah form aktif & entries dalam rentang) */
    public function byDepartment(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        $cacheKey = 'dash_by_dept:'.md5(json_encode([$from,$to,$deptId,$formId]));

        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            // forms aktif per dept
            $forms = Form::select('department_id', DB::raw('COUNT(*) as total_forms'), DB::raw('SUM(is_active=1) as active_forms'))
                ->when($deptId, fn($q)=>$q->where('department_id',$deptId))
                ->when($formId, fn($q)=>$q->where('id',$formId))
                ->groupBy('department_id')->get()->keyBy('department_id');

            // entries per dept (dari join)
            $entries = FormEntry::select('forms.department_id', DB::raw('COUNT(form_entries.id) as total_entries'))
                ->join('forms','forms.id','=','form_entries.form_id')
                ->when($deptId, fn($q)=>$q->where('forms.department_id',$deptId))
                ->when($formId, fn($q)=>$q->where('forms.id',$formId))
                ->when($from, fn($q)=>$q->where('form_entries.created_at','>=',$from->startOfDay()))
                ->when($to,   fn($q)=>$q->where('form_entries.created_at','<=',$to->endOfDay()))
                ->groupBy('forms.department_id')
                ->get()->keyBy('department_id');

            $departments = Department::orderBy('name')->get(['id','name']);
            $result = $departments->map(function($d) use ($forms,$entries){
                $f = $forms[$d->id] ?? null;
                $e = $entries[$d->id] ?? null;
                return [
                    'department'    => $d->name,
                    'total_forms'   => (int)($f->total_forms ?? 0),
                    'active_forms'  => (int)($f->active_forms ?? 0),
                    'total_entries' => (int)($e->total_entries ?? 0),
                ];
            })->filter(fn($x)=>$x['total_forms']>0 || $x['total_entries']>0)->values();

            return ['rows'=>$result];
        });

        return response()->json($data);
    }

    /** helper */
    private function extractFilters(Request $r): array
    {
        $deptId = $r->integer('department_id') ?: null;
        $formId = $r->integer('form_id') ?: null;

        $from = $r->filled('date_from') ? Carbon::parse($r->input('date_from')) : null;
        $to   = $r->filled('date_to')   ? Carbon::parse($r->input('date_to'))   : null;

        return [$from, $to, $deptId, $formId];
    }
}
