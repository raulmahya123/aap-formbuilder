<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{Form, FormEntry, Department, Document, DocumentTemplate, DocumentAcl};

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
    /** Kartu ringkas: total form, total entries, aktif form, pengguna unik
 *  + totalDocuments, totalTemplates
 *  + recentDocuments, recentTemplates, recentAclChanges (default 30 hari)
 */
public function summary(Request $r)
{
    [$from, $to, $deptId, $formId] = $this->extractFilters($r);

    // window untuk "recent" (bisa override pakai ?recent_days=14)
    $recentDays = (int)($r->recent_days ?? 30);
    if ($recentDays < 1) $recentDays = 30;

    // Kalau user kasih date_from/date_to → recent pakai window itu.
    // Kalau tidak → recent pakai now()-recentDays..now()
    $recentFrom = $from ? (clone $from)->startOfDay() : now()->subDays($recentDays-1)->startOfDay();
    $recentTo   = $to   ? (clone $to)->endOfDay()     : now()->endOfDay();

    $cacheKey = 'dash_summary:'.md5(json_encode([
        $from?->toDateString(), $to?->toDateString(), $deptId, $formId,
        'recentDays'=>$recentDays, 'recentFrom'=>$recentFrom->toDateTimeString(), 'recentTo'=>$recentTo->toDateTimeString(),
    ]));

    $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId,$recentFrom,$recentTo){
        // Base queries
        $formQuery  = Form::query();
        $entryQuery = FormEntry::query();

        if ($deptId) {
            $formQuery->where('department_id', $deptId);
            $entryQuery->whereHas('form', fn($q) => $q->where('department_id', $deptId));
        }
        if ($formId) {
            $formQuery->where('id', $formId);
            $entryQuery->where('form_id', $formId);
        }
        if ($from) { $entryQuery->where('created_at', '>=', (clone $from)->startOfDay()); }
        if ($to)   { $entryQuery->where('created_at', '<=', (clone $to)->endOfDay());   }

        // KPI existing
        $totalForms   = (clone $formQuery)->count();
        $activeForms  = (clone $formQuery)->where('is_active', true)->count();
        $totalEntries = (clone $entryQuery)->count();
        $uniqueUsers  = (clone $entryQuery)->distinct('user_id')->count('user_id');

        // KPI Documents / Templates (total)
        $docsTotalQ = Document::query();
        if ($deptId) $docsTotalQ->where('department_id', $deptId);
        $totalDocuments = (clone $docsTotalQ)->count();

        $tmplTotalQ = DocumentTemplate::query();
        $totalTemplates = (clone $tmplTotalQ)->count();

        // KPI Recent (menghormati filter department & date range jika ada)
        $recentDocsQ = Document::query()
            ->when($deptId, fn($q) => $q->where('department_id', $deptId))
            ->whereBetween('created_at', [$recentFrom, $recentTo]);

        $recentTmplQ = DocumentTemplate::query()
            ->whereBetween('updated_at', [$recentFrom, $recentTo]);

        $recentAclQ = DocumentAcl::query()
            ->when($deptId, function($q) use ($deptId) {
                $q->where(function($qq) use ($deptId) {
                    $qq->where('department_id', $deptId)
                       ->orWhereIn('document_id', Document::where('department_id', $deptId)->pluck('id'));
                });
            })
            ->whereBetween('created_at', [$recentFrom, $recentTo]);

        $recentDocuments  = (clone $recentDocsQ)->count();
        $recentTemplates  = (clone $recentTmplQ)->count();
        $recentAclChanges = (clone $recentAclQ)->count();

        return compact(
            'totalForms','activeForms','totalEntries','uniqueUsers',
            'totalDocuments','totalTemplates',
            'recentDocuments','recentTemplates','recentAclChanges'
        );
    });

    return response()->json($data);
}


    /** Tren entries per hari (default 30 hari terakhir) */
    public function entriesByDay(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        if (!$from && !$to) {
            $to   = now();
            $from = now()->copy()->subDays(29);
        }

        $cacheKey = 'dash_entries_by_day:'.md5(json_encode([$from,$to,$deptId,$formId]));
        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            $q = FormEntry::selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->when($deptId, fn($qq) => $qq->whereHas('form', fn($f) => $f->where('department_id', $deptId)))
                ->when($formId, fn($qq) => $qq->where('form_id', $formId))
                ->when($from,   fn($qq) => $qq->where('created_at', '>=', (clone $from)->startOfDay()))
                ->when($to,     fn($qq) => $qq->where('created_at', '<=', (clone $to)->endOfDay()))
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            // Lengkapi tanggal kosong supaya chart mulus (hindari null property access)
            $map    = $q->keyBy('d');
            $labels = [];
            $series = [];

            $start = (clone $from)->startOfDay();
            $end   = (clone $to)->endOfDay();

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key   = $d->toDateString();
                $row   = $map->get($key);              // bisa null
                $count = $row ? (int) $row->c : 0;     // aman
                $labels[] = $key;
                $series[] = $count;
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
                ->when($deptId, fn($qq) => $qq->whereHas('form', fn($f) => $f->where('department_id', $deptId)))
                ->when($formId, fn($qq) => $qq->where('form_id', $formId))
                ->when($from,   fn($qq) => $qq->where('created_at', '>=', (clone $from)->startOfDay()))
                ->when($to,     fn($qq) => $qq->where('created_at', '<=', (clone $to)->endOfDay()))
                ->groupBy('form_id')
                ->orderByDesc('c')
                ->limit(10)
                ->get();

            $formTitles = Form::whereIn('id', $rows->pluck('form_id'))->pluck('title','id');

            $labels = $rows->map(fn($r) => $formTitles->get($r->form_id, 'Form #'.$r->form_id));
            $series = $rows->pluck('c')->map(fn($v) => (int) $v);

            return ['labels' => $labels->values(), 'series' => $series->values()];
        });

        return response()->json($data);
    }

    /** Rekap per-department (jumlah form aktif & entries dalam rentang) */
    public function byDepartment(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        $cacheKey = 'dash_by_dept:'.md5(json_encode([$from,$to,$deptId,$formId]));

        $data = Cache::remember($cacheKey, 60, function() use ($from,$to,$deptId,$formId){
            // forms per dept (aktif & total)
            $forms = Form::select(
                    'department_id',
                    DB::raw('COUNT(*) as total_forms'),
                    DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_forms')
                )
                ->when($deptId, fn($q) => $q->where('department_id', $deptId))
                ->when($formId, fn($q) => $q->where('id', $formId))
                ->groupBy('department_id')
                ->get()
                ->keyBy('department_id');

            // entries per dept
            $entries = FormEntry::select('forms.department_id', DB::raw('COUNT(form_entries.id) as total_entries'))
                ->join('forms','forms.id','=','form_entries.form_id')
                ->when($deptId, fn($q) => $q->where('forms.department_id', $deptId))
                ->when($formId, fn($q) => $q->where('forms.id', $formId))
                ->when($from,   fn($q) => $q->where('form_entries.created_at', '>=', (clone $from)->startOfDay()))
                ->when($to,     fn($q) => $q->where('form_entries.created_at', '<=', (clone $to)->endOfDay()))
                ->groupBy('forms.department_id')
                ->get()
                ->keyBy('department_id');

            $departments = Department::orderBy('name')->get(['id','name']);

            $rows = $departments->map(function($d) use ($forms,$entries){
                $f = $forms->get($d->id);
                $e = $entries->get($d->id);

                return [
                    'department'    => $d->name,
                    'total_forms'   => (int) ($f->total_forms  ?? 0),
                    'active_forms'  => (int) ($f->active_forms ?? 0),
                    'total_entries' => (int) ($e->total_entries ?? 0),
                ];
            })
            ->filter(fn($x) => $x['total_forms'] > 0 || $x['total_entries'] > 0)
            ->values();

            return ['rows' => $rows];
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
