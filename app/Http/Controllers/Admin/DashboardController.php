<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{Form, FormEntry, Department, Document, DocumentTemplate, DocumentAcl, Site, WellnessResponse, HipoReport, CcmReport, IndicatorDaily, DailyNote};

class DashboardController extends Controller
{
    private const DASHBOARD_CACHE_TTL = 60;

    public function index(Request $r)
    {
        $allowed = $r->user()->allowedSiteIds();
        $sitesQuery = Site::query()->with('company:id,code,name')->orderBy('code');
        if (is_array($allowed)) $sitesQuery->whereIn('id', $allowed ?: [-1]);
        $sites = $sitesQuery->get(['id','code','name','company_id']);

        $selectedSiteId = \App\Support\SiteSelection::id($r);
        if ($selectedSiteId && !$r->user()->hasSiteAccess($selectedSiteId)) abort(403);
        $selectedSite = $selectedSiteId ? $sites->firstWhere('id', $selectedSiteId) : null;
        abort_if($selectedSiteId && !$selectedSite, 404);

        $siteFilter = fn ($q, $column = 'site_id') => is_array($allowed)
            ? $q->whereIn($column, $allowed ?: [-1])
            : $q;
        $siteFilterSelected = fn ($q, $column = 'site_id') => $selectedSiteId
            ? $q->where($column, $selectedSiteId)
            : $q;

        $metrics = [
            'wellness' => $this->wellnessQuery($selectedSite, $allowed)->count(),
            'hipo' => $siteFilterSelected($siteFilter(HipoReport::query()))->count(),
            'kpi' => $siteFilterSelected($siteFilter(IndicatorDaily::query()))->count(),
            'notes' => $siteFilterSelected($siteFilter(DailyNote::query()))->count(),
            'ccm' => $this->ccmCount($selectedSite, $allowed),
        ];

        return view('admin.dashboard.index', compact('sites', 'selectedSiteId', 'selectedSite', 'metrics'));
    }

    private function wellnessQuery(?Site $site, ?array $allowed)
    {
        $q = WellnessResponse::query();
        if ($site) return $q->where(function ($w) use ($site) {
            $w->where('site', $site->name)->orWhere('site', $site->code);
        });
        if (is_array($allowed)) {
            $labels = Site::whereIn('id', $allowed ?: [-1])->get(['name','code'])
                ->flatMap(fn ($s) => [$s->name, $s->code])->filter()->values()->all();
            $q->whereIn('site', $labels ?: ['__none__']);
        }
        return $q;
    }

    private function ccmCount(?Site $site, ?array $allowed): int
    {
        if (!$site && is_array($allowed)) {
            $labels = Site::whereIn('id', $allowed ?: [-1])->with('company:id,code,name')->get()
                ->map(fn ($s) => $s->company ? (($s->company->code ?: $s->company->name) . '-' . ($s->code ?: $s->name)) : ($s->code ?: $s->name));
            return CcmReport::whereIn('jobsite', $labels->all())->count();
        }
        if ($site) {
            $label = $site->company ? (($site->company->code ?: $site->company->name) . '-' . ($site->code ?: $site->name)) : ($site->code ?: $site->name);
            return CcmReport::where('jobsite', $label)->count();
        }
        return CcmReport::count();
    }

    /** Kartu ringkas: total form, total entries, aktif form, pengguna unik */
    /** Kartu ringkas: total form, total entries, aktif form, pengguna unik
 *  + totalDocuments, totalTemplates
 *  + recentDocuments, recentTemplates, recentAclChanges (default 30 hari)
 */
public function summary(Request $r)
{
    [$from, $to, $deptId, $formId] = $this->extractFilters($r);
    $user = $r->user();

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
        'user' => $user->id,
    ]));

    $data = $this->rememberDashboard($cacheKey, function() use ($user,$from,$to,$deptId,$formId,$recentFrom,$recentTo){
        // Base queries
        $formQuery  = Form::accessibleTo($user);
        $entryQuery = FormEntry::query();

        if ($deptId) {
            $formQuery->where('department_id', $deptId);
            $entryQuery->whereIn('form_id', Form::accessibleTo($user)
                ->select('id')->where('department_id', $deptId));
        }
        if (!$deptId) $entryQuery->whereIn('form_id', Form::accessibleTo($user)->select('id'));
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
                       ->orWhereIn('document_id', Document::query()
                           ->select('id')
                           ->where('department_id', $deptId));
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
        $user = $r->user();
        if (!$from && !$to) {
            $to   = now();
            $from = now()->copy()->subDays(29);
        }

        $cacheKey = 'dash_entries_by_day:'.md5(json_encode([$from,$to,$deptId,$formId]));
        $data = $this->rememberDashboard($cacheKey, function() use ($user,$from,$to,$deptId,$formId){
            $q = FormEntry::selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->when($deptId, fn($qq) => $qq->whereIn('form_id', Form::query()
                    ->select('id')
                    ->where('department_id', $deptId)))
                ->whereIn('form_id', Form::accessibleTo($user)->select('id'))
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
        $user = $r->user();
        $topN = max(3, min(20, (int) ($r->integer('topN') ?: 10)));
        $cacheKey = 'dash_top_forms:'.md5(json_encode([$from,$to,$deptId,$formId,$topN]));

        $data = $this->rememberDashboard($cacheKey, function() use ($user,$from,$to,$deptId,$formId,$topN){
            $rows = DB::table('form_entries')
                ->join('forms', 'forms.id', '=', 'form_entries.form_id')
                ->when($deptId, fn($q) => $q->where('forms.department_id', $deptId))
                ->whereIn('forms.id', Form::accessibleTo($user)->select('id'))
                ->when($formId, fn($q) => $q->where('form_entries.form_id', $formId))
                ->when($from,   fn($q) => $q->where('form_entries.created_at', '>=', (clone $from)->startOfDay()))
                ->when($to,     fn($q) => $q->where('form_entries.created_at', '<=', (clone $to)->endOfDay()))
                ->groupBy('form_entries.form_id', 'forms.title')
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit($topN)
                ->select([
                    'form_entries.form_id',
                    'forms.title',
                    DB::raw('COUNT(*) as c'),
                ])
                ->get();

            $labels = $rows->map(fn($r) => $r->title ?: 'Form #'.$r->form_id);
            $series = $rows->pluck('c')->map(fn($v) => (int) $v);

            return ['labels' => $labels->values(), 'series' => $series->values()];
        });

        return response()->json($data);
    }

    /** Rekap per-department (jumlah form aktif & entries dalam rentang) */
    public function byDepartment(Request $r)
    {
        [$from, $to, $deptId, $formId] = $this->extractFilters($r);
        $user = $r->user();
        $cacheKey = 'dash_by_dept:'.md5(json_encode([$from,$to,$deptId,$formId]));

        $data = $this->rememberDashboard($cacheKey, function() use ($user,$from,$to,$deptId,$formId){
            // forms per dept (aktif & total)
            $forms = Form::select(
                    'department_id',
                    DB::raw('COUNT(*) as total_forms'),
                    DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_forms')
                )
                ->when($deptId, fn($q) => $q->where('department_id', $deptId))
                ->whereIn('id', Form::accessibleTo($user)->select('id'))
                ->when($formId, fn($q) => $q->where('id', $formId))
                ->groupBy('department_id')
                ->get()
                ->keyBy('department_id');

            // entries per dept
            $entries = FormEntry::select('forms.department_id', DB::raw('COUNT(form_entries.id) as total_entries'))
                ->join('forms','forms.id','=','form_entries.form_id')
                ->when($deptId, fn($q) => $q->where('forms.department_id', $deptId))
                ->whereIn('forms.id', Form::accessibleTo($user)->select('id'))
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

    public function byAggregate(Request $r)
{
    [$from, $to, $deptId, $formId] = $this->extractFilters($r);
    $group = $r->string('group', 'department');
    $cacheKey = 'dash_by_agg:'.md5(json_encode([$group, $from?->toDateString(), $to?->toDateString(), $deptId, $formId]));

    $payload = $this->rememberDashboard($cacheKey, function() use ($group, $from, $to, $deptId, $formId) {

        if ($group === 'form') {
            // Rows per Form dengan total entries pada rentang & status aktif
            $rows = DB::table('forms')
                ->leftJoin('form_entries', function($j) use ($from, $to) {
                    $j->on('form_entries.form_id','=','forms.id');
                    if ($from) {
                        $j->where('form_entries.created_at', '>=', (clone $from)->startOfDay());
                    }
                    if ($to) {
                        $j->where('form_entries.created_at', '<=', (clone $to)->endOfDay());
                    }
                })
                ->when($deptId, fn($q) => $q->where('forms.department_id', $deptId))
                ->when($formId, fn($q) => $q->where('forms.id', $formId))
                ->groupBy('forms.id','forms.title','forms.is_active','forms.department_id')
                ->select([
                    'forms.id',
                    'forms.title',
                    'forms.is_active',
                    'forms.department_id',
                    DB::raw('COUNT(form_entries.id) as total_entries'),
                ])
                ->orderByDesc(DB::raw('COUNT(form_entries.id)'))
                ->get();

            $deptNames = \App\Models\Department::pluck('name','id');

            $columns = [
                ['key'=>'name',          'label'=>'Form',       'align'=>'left'],
                ['key'=>'department',    'label'=>'Department', 'align'=>'left'],
                ['key'=>'is_active',     'label'=>'Active',     'align'=>'left'],
                ['key'=>'total_entries', 'label'=>'Total Entries','align'=>'right','format'=>'number'],
            ];

            $rowsOut = $rows->map(function($r) use ($deptNames){
                return [
                    '__key'        => 'form_'.$r->id,
                    'name'         => $r->title,
                    'department'   => $deptNames[$r->department_id] ?? '-',
                    'is_active'    => $r->is_active ? 'Ya' : 'Tidak',
                    'total_entries'=> (int)$r->total_entries,
                ];
            })->values();

            return ['columns'=>$columns, 'rows'=>$rowsOut];
        }

        if ($group === 'document') {
            // Rows per Document Template: total dokumen dibuat pada rentang
            $rows = DB::table('documents')
                ->when($deptId, fn($q) => $q->where('documents.department_id', $deptId))
                ->when($from,   fn($q) => $q->where('documents.created_at', '>=', (clone $from)->startOfDay()))
                ->when($to,     fn($q) => $q->where('documents.created_at', '<=', (clone $to)->endOfDay()))
                ->groupBy('documents.template_id')
                ->select([
                    'documents.template_id as template_id',
                    DB::raw('COUNT(*) as total_documents'),
                ])
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->get();

            $tmplNames = \App\Models\DocumentTemplate::whereIn('id', $rows->pluck('template_id')->filter())
                ->pluck('name','id');

            $columns = [
                ['key'=>'template',       'label'=>'Template',        'align'=>'left'],
                ['key'=>'total_documents','label'=>'Total Documents', 'align'=>'right','format'=>'number'],
            ];

            $rowsOut = $rows->map(function($r) use ($tmplNames){
                return [
                    '__key'          => 'tmpl_'.$r->template_id,
                    'template'       => $tmplNames[$r->template_id] ?? '—',
                    'total_documents'=> (int)$r->total_documents,
                ];
            })->values();

            return ['columns'=>$columns, 'rows'=>$rowsOut];
        }

        // default: group by department (lama)
        $forms = DB::table('forms')
            ->when($deptId, fn($q) => $q->where('department_id', $deptId))
            ->when($formId, fn($q) => $q->where('id', $formId))
            ->groupBy('department_id')
            ->select([
                'department_id',
                DB::raw('COUNT(*) as total_forms'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_forms')
            ])->get()->keyBy('department_id');

        $entries = DB::table('form_entries')
            ->join('forms','forms.id','=','form_entries.form_id')
            ->when($deptId, fn($q) => $q->where('forms.department_id', $deptId))
            ->when($formId, fn($q) => $q->where('forms.id', $formId))
            ->when($from,   fn($q) => $q->where('form_entries.created_at', '>=', (clone $from)->startOfDay()))
            ->when($to,     fn($q) => $q->where('form_entries.created_at', '<=', (clone $to)->endOfDay()))
            ->groupBy('forms.department_id')
            ->select(['forms.department_id', DB::raw('COUNT(form_entries.id) as total_entries')])
            ->get()->keyBy('department_id');

        $depts = \App\Models\Department::orderBy('name')->get(['id','name']);

        $columns = [
            ['key'=>'name',          'label'=>'Department',   'align'=>'left'],
            ['key'=>'total_forms',   'label'=>'Total Forms',  'align'=>'right','format'=>'number'],
            ['key'=>'active_forms',  'label'=>'Active Forms', 'align'=>'right','format'=>'number'],
            ['key'=>'total_entries', 'label'=>'Total Entries','align'=>'right','format'=>'number'],
        ];

        $rowsOut = $depts->map(function($d) use ($forms,$entries){
            $f = $forms->get($d->id);
            $e = $entries->get($d->id);
            $row = [
                '__key'        => 'dept_'.$d->id,
                'name'         => $d->name,
                'total_forms'  => (int)($f->total_forms  ?? 0),
                'active_forms' => (int)($f->active_forms ?? 0),
                'total_entries'=> (int)($e->total_entries ?? 0),
            ];
            return ($row['total_forms']>0 || $row['total_entries']>0) ? $row : null;
        })->filter()->values();

        return ['columns'=>$columns, 'rows'=>$rowsOut];
    });

    // inject formatter hint untuk angka (opsional)
    // front-end sudah handle via col.format === 'number'
    return response()->json($payload);
}

    private function rememberDashboard(string $key, callable $callback): mixed
    {
        return Cache::store('file')->remember($key, self::DASHBOARD_CACHE_TTL, $callback);
    }
}
