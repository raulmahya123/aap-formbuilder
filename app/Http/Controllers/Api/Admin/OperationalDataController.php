<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CcmReport;
use App\Models\Form;
use App\Models\HipoReport;
use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use App\Support\ShiftWindow;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperationalDataController extends Controller
{
    private const MAX_PER_PAGE = 100;

    public function hipoReports(Request $request): JsonResponse
    {
        $query = HipoReport::query()
            ->with(['site.company:id,code,name', 'user:id,name,email'])
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->when($request->filled('risk_level'), fn (Builder $q) => $q->where('risk_level', $request->string('risk_level')))
            ->when($request->filled('category'), fn (Builder $q) => $q->where('category', $request->string('category')))
            ->when($request->filled('site_id'), fn (Builder $q) => $q->where('site_id', $request->integer('site_id')))
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('report_time', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('report_time', '<=', $request->date('date_to')))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%' . trim((string) $request->query('q')) . '%';
                $q->where(function (Builder $w) use ($term) {
                    $w->where('jobsite', 'like', $term)
                        ->orWhere('reporter_name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('report_time')
            ->orderByDesc('id');

        $reports = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($reports, fn (HipoReport $report) => $this->hipoToArray($report));
    }

    public function hipoReport(HipoReport $hipo): JsonResponse
    {
        $hipo->load(['site.company:id,code,name', 'user:id,name,email']);

        return response()->json(['data' => $this->hipoToArray($hipo)]);
    }

    public function ccmReports(Request $request): JsonResponse
    {
        $query = CcmReport::query()
            ->when($request->filled('jobsite'), fn (Builder $q) => $q->where('jobsite', $request->string('jobsite')))
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('waktu_pelaporan', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('waktu_pelaporan', '<=', $request->date('date_to')))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%' . trim((string) $request->query('q')) . '%';
                $q->where(function (Builder $w) use ($term) {
                    $w->where('jobsite', 'like', $term)
                        ->orWhere('nama_pelapor', 'like', $term);
                });
            })
            ->orderByDesc('waktu_pelaporan')
            ->orderByDesc('id');

        $reports = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($reports, fn (CcmReport $report) => $this->ccmToArray($report));
    }

    public function ccmReport(CcmReport $ccmReport): JsonResponse
    {
        return response()->json(['data' => $this->ccmToArray($ccmReport)]);
    }

    public function forms(Request $request): JsonResponse
    {
        $query = Form::query()
            ->with(['department:id,name,slug', 'creator:id,name,email', 'company:id,code,name', 'site:id,code,name,company_id'])
            ->when($request->filled('department_id'), fn (Builder $q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->filled('company_id'), fn (Builder $q) => $q->where('company_id', $request->integer('company_id')))
            ->when($request->filled('site_id'), fn (Builder $q) => $q->where('site_id', $request->integer('site_id')))
            ->when($request->filled('doc_type'), fn (Builder $q) => $q->docType($request->query('doc_type')))
            ->when($request->filled('type'), fn (Builder $q) => $q->type($request->query('type')))
            ->when($request->has('active'), fn (Builder $q) => $q->where('is_active', $request->boolean('active')))
            ->when($request->filled('q'), fn (Builder $q) => $q->search($request->query('q')))
            ->latest();

        $forms = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($forms, fn (Form $form) => $this->formToArray($form));
    }

    public function form(Form $form): JsonResponse
    {
        $form->load(['department:id,name,slug', 'creator:id,name,email', 'company:id,code,name', 'site:id,code,name,company_id']);

        return response()->json(['data' => $this->formToArray($form)]);
    }

    public function indicatorGroups(Request $request): JsonResponse
    {
        $includeIndicators = $request->boolean('include_indicators');

        $query = IndicatorGroup::query()
            ->withCount('indicators')
            ->when($includeIndicators, function (Builder $q) {
                $q->with(['indicators' => fn ($indicatorQ) => $indicatorQ->orderBy('order_index')->orderBy('id')]);
            })
            ->when($request->has('active'), fn (Builder $q) => $q->where('is_active', $request->boolean('active')))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%' . trim((string) $request->query('q')) . '%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $term)->orWhere('code', 'like', $term));
            })
            ->orderBy('order_index')
            ->orderBy('id');

        $groups = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($groups, fn (IndicatorGroup $group) => $this->indicatorGroupToArray($group, $includeIndicators));
    }

    public function indicatorGroup(IndicatorGroup $group): JsonResponse
    {
        $group->load(['indicators' => fn ($q) => $q->orderBy('order_index')->orderBy('id')]);
        $group->loadCount('indicators');

        return response()->json(['data' => $this->indicatorGroupToArray($group, true)]);
    }

    public function indicators(Request $request): JsonResponse
    {
        $query = Indicator::query()
            ->with('group:id,name,code')
            ->when($request->filled('group_id'), fn (Builder $q) => $q->where('indicator_group_id', $request->integer('group_id')))
            ->when($request->has('active'), fn (Builder $q) => $q->where('is_active', $request->boolean('active')))
            ->when($request->has('derived'), fn (Builder $q) => $q->where('is_derived', $request->boolean('derived')))
            ->when($request->filled('data_type'), fn (Builder $q) => $q->where('data_type', $request->query('data_type')))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%' . trim((string) $request->query('q')) . '%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $term)->orWhere('code', 'like', $term));
            })
            ->orderBy('indicator_group_id')
            ->orderBy('order_index')
            ->orderBy('id');

        $indicators = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($indicators, fn (Indicator $indicator) => $this->indicatorToArray($indicator));
    }

    public function indicator(Indicator $indicator): JsonResponse
    {
        $indicator->load('group:id,name,code');

        return response()->json(['data' => $this->indicatorToArray($indicator)]);
    }

    public function dailyCreate(Request $request): JsonResponse
    {
        $date = $request->input('date', now(config('shifts.timezone', 'Asia/Jakarta'))->toDateString());
        $allowedSiteIds = $this->allowedSiteIds($request);

        $sitesQuery = Site::query()->with('company:id,code,name')->orderBy('code')->orderBy('name');
        if (is_array($allowedSiteIds)) {
            $sitesQuery->whereIn('id', $allowedSiteIds ?: [-1]);
        }

        $groups = IndicatorGroup::query()
            ->with(['indicators' => function ($q) {
                $q->where('is_active', true)->orderBy('order_index')->orderBy('id');
            }])
            ->where('is_active', true)
            ->orderBy('order_index')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => [
                'date' => Carbon::parse($date)->toDateString(),
                'sites' => $sitesQuery->get(['id', 'code', 'name', 'company_id'])->map(fn (Site $site) => $this->siteToArray($site))->values(),
                'groups' => $groups->map(fn (IndicatorGroup $group) => $this->indicatorGroupToArray($group, true))->values(),
                'shift_info' => ShiftWindow::detect($date),
            ],
        ]);
    }

    public function dailyRows(Request $request): JsonResponse
    {
        $allowedSiteIds = $this->allowedSiteIds($request);
        $siteId = $request->integer('site_id') ?: null;

        $query = IndicatorDaily::query()
            ->with(['site.company:id,code,name', 'indicator.group:id,name,code'])
            ->when($request->filled('date'), fn (Builder $q) => $q->whereDate('date', $request->date('date')))
            ->when(!$request->filled('date'), function (Builder $q) use ($request) {
                $month = (int) $request->input('month', now()->month);
                $year = (int) $request->input('year', now()->year);
                $start = Carbon::create($year, $month, 1)->startOfDay();
                $end = (clone $start)->endOfMonth();

                $q->whereBetween('date', [$start, $end]);
            })
            ->when($siteId, fn (Builder $q) => $q->where('site_id', $siteId))
            ->when($request->filled('indicator_id'), fn (Builder $q) => $q->where('indicator_id', $request->integer('indicator_id')))
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (is_array($allowedSiteIds)) {
            $query->whereIn('site_id', $allowedSiteIds ?: [-1]);
        }

        $rows = $query->paginate($this->perPage($request))->withQueryString();

        return $this->paginated($rows, fn (IndicatorDaily $daily) => $this->dailyToArray($daily));
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 15), 1), self::MAX_PER_PAGE);
    }

    private function paginated($paginator, callable $mapper): JsonResponse
    {
        return response()->json([
            'data' => $paginator->getCollection()->map($mapper)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    private function hipoToArray(HipoReport $report): array
    {
        return [
            'id' => $report->id,
            'site_id' => $report->site_id,
            'jobsite' => $report->jobsite,
            'site' => $report->relationLoaded('site') && $report->site ? $this->siteToArray($report->site) : null,
            'reporter' => $report->relationLoaded('user') && $report->user ? [
                'id' => $report->user->id,
                'name' => $report->user->name,
                'email' => $report->user->email,
            ] : null,
            'reporter_name' => $report->reporter_name,
            'report_time' => $report->report_time?->toISOString(),
            'jenis_hipo' => $report->jenis_hipo,
            'shift' => $report->shift,
            'source' => $report->source,
            'category' => $report->category,
            'description' => $report->description,
            'potential_consequence' => $report->potential_consequence,
            'risk_level' => $report->risk_level,
            'kta' => $report->kta,
            'tta' => $report->tta,
            'stop_work' => (bool) $report->stop_work,
            'controls' => [
                'engineering' => $report->control_engineering,
                'administrative' => $report->control_administrative,
                'work_practice' => $report->control_work_practice,
                'ppe' => $report->control_ppe,
            ],
            'pics' => [
                'engineering' => $report->pic_engineering,
                'administrative' => $report->pic_administrative,
                'work_practice' => $report->pic_work_practice,
                'ppe' => $report->pic_ppe,
            ],
            'evidences' => [
                'engineering' => $this->publicFileToArray($report->evidence_engineering),
                'administrative' => $this->publicFileToArray($report->evidence_administrative),
                'work_practice' => $this->publicFileToArray($report->evidence_work_practice),
                'ppe' => $this->publicFileToArray($report->evidence_ppe),
            ],
            'status' => $report->status,
            'admin_note' => $report->admin_note,
            'created_at' => $report->created_at?->toISOString(),
            'updated_at' => $report->updated_at?->toISOString(),
        ];
    }

    private function ccmToArray(CcmReport $report): array
    {
        $data = $report->toArray();
        $data['evidence_urls'] = collect($report->getAttributes())
            ->filter(fn ($value, string $field) => str_ends_with($field, '_evidence') && filled($value))
            ->map(fn ($value) => $this->publicFileUrl($value))
            ->all();

        return $data;
    }

    private function formToArray(Form $form): array
    {
        return [
            'id' => $form->id,
            'title' => $form->title,
            'slug' => $form->slug,
            'doc_type' => $form->doc_type,
            'type' => $form->type,
            'description' => $form->description,
            'schema' => $form->schema,
            'pdf_path' => $form->pdf_path,
            'file_url' => $form->pdf_path ? route('admin.forms.file', $form) : null,
            'download_url' => $form->pdf_path ? route('admin.forms.download', $form) : null,
            'is_active' => (bool) $form->is_active,
            'company' => $form->relationLoaded('company') && $form->company ? [
                'id' => $form->company->id,
                'code' => $form->company->code,
                'name' => $form->company->name,
            ] : null,
            'site' => $form->relationLoaded('site') && $form->site ? $this->siteToArray($form->site) : null,
            'department' => $form->relationLoaded('department') && $form->department ? [
                'id' => $form->department->id,
                'name' => $form->department->name,
                'slug' => $form->department->slug,
            ] : null,
            'creator' => $form->relationLoaded('creator') && $form->creator ? [
                'id' => $form->creator->id,
                'name' => $form->creator->name,
                'email' => $form->creator->email,
            ] : null,
            'created_at' => $form->created_at?->toISOString(),
            'updated_at' => $form->updated_at?->toISOString(),
        ];
    }

    private function indicatorGroupToArray(IndicatorGroup $group, bool $includeIndicators = false): array
    {
        $data = [
            'id' => $group->id,
            'name' => $group->name,
            'code' => $group->code,
            'order_index' => (int) $group->order_index,
            'is_active' => (bool) $group->is_active,
            'indicators_count' => $group->indicators_count ?? null,
            'created_at' => $group->created_at?->toISOString(),
            'updated_at' => $group->updated_at?->toISOString(),
        ];

        if ($includeIndicators && $group->relationLoaded('indicators')) {
            $data['indicators'] = $group->indicators->map(fn (Indicator $indicator) => $this->indicatorToArray($indicator))->values();
        }

        return $data;
    }

    private function indicatorToArray(Indicator $indicator): array
    {
        return [
            'id' => $indicator->id,
            'indicator_group_id' => $indicator->indicator_group_id,
            'group' => $indicator->relationLoaded('group') && $indicator->group ? [
                'id' => $indicator->group->id,
                'name' => $indicator->group->name,
                'code' => $indicator->group->code,
            ] : null,
            'name' => $indicator->name,
            'code' => $indicator->code,
            'data_type' => $indicator->data_type,
            'agg' => $indicator->agg,
            'unit' => $indicator->unit,
            'order_index' => (int) $indicator->order_index,
            'is_derived' => (bool) $indicator->is_derived,
            'formula' => $indicator->formula,
            'is_active' => (bool) $indicator->is_active,
            'threshold' => $indicator->threshold,
            'weight' => $indicator->weight,
            'created_at' => $indicator->created_at?->toISOString(),
            'updated_at' => $indicator->updated_at?->toISOString(),
        ];
    }

    private function dailyToArray(IndicatorDaily $daily): array
    {
        return [
            'id' => $daily->id,
            'date' => $daily->date?->toDateString(),
            'site_id' => $daily->site_id,
            'site' => $daily->relationLoaded('site') && $daily->site ? $this->siteToArray($daily->site) : null,
            'indicator_id' => $daily->indicator_id,
            'indicator' => $daily->relationLoaded('indicator') && $daily->indicator ? $this->indicatorToArray($daily->indicator) : null,
            'value' => $daily->value,
            'note' => $daily->note,
            'shift' => $daily->shift,
            'input_at' => $daily->input_at?->toISOString(),
            'is_late' => (bool) $daily->is_late,
            'created_at' => $daily->created_at?->toISOString(),
            'updated_at' => $daily->updated_at?->toISOString(),
        ];
    }

    private function siteToArray(Site $site): array
    {
        return [
            'id' => $site->id,
            'code' => $site->code,
            'name' => $site->name,
            'company_id' => $site->company_id,
            'company' => $site->relationLoaded('company') && $site->company ? [
                'id' => $site->company->id,
                'code' => $site->company->code,
                'name' => $site->company->name,
            ] : null,
        ];
    }

    private function publicFileToArray(?string $path): ?array
    {
        if (!$path) {
            return null;
        }

        return [
            'path' => $path,
            'url' => $this->publicFileUrl($path),
        ];
    }

    private function publicFileUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }

    private function allowedSiteIds(Request $request): ?array
    {
        $user = $request->user();
        if (!$user) {
            return [];
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return null;
        }

        if (method_exists($user, 'sites')) {
            return $user->sites()->pluck('sites.id')->all();
        }

        return [];
    }
}
