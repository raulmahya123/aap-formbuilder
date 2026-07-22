<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CcmReport, Company, Form, HipoReport, Indicator, IndicatorGroup, Site};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Database\Eloquent\Builder;

class PublicDataController extends Controller
{
    private const MAX_PER_PAGE = 100;

    // === COMPANIES ===

    public function companies(Request $request): JsonResponse
    {
        $query = Company::with('sites')
            ->when($request->filled('status'), fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        return $this->paginated($query->paginate($this->perPage($request)), fn (Company $company) => $this->formatCompany($company));
    }

    public function company(Company $company): JsonResponse
    {
        $company->load(['sites', 'addresses']);

        return $this->success($this->formatCompany($company));
    }

    // === INDICATOR GROUPS ===

    public function indicatorGroups(Request $request): JsonResponse
    {
        $query = IndicatorGroup::query()
            ->when($request->boolean('include_indicators'), fn (Builder $q) => $q->with('indicators'))
            ->when($request->filled('active'), fn (Builder $q, string $active) => $q->where('active', $active))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('order_index')
            ->orderBy('id');

        return $this->paginated($query->paginate($this->perPage($request)), fn (IndicatorGroup $group) => $this->formatGroup($group));
    }

    public function indicatorGroup(IndicatorGroup $group): JsonResponse
    {
        $group->load('indicators');

        return $this->success($this->formatGroup($group));
    }

    // === INDICATORS ===

    public function indicators(Request $request): JsonResponse
    {
        $query = Indicator::with('group')
            ->when($request->filled('group_id'), fn (Builder $q, string $groupId) => $q->where('group_id', $groupId))
            ->when($request->filled('active'), fn (Builder $q, string $active) => $q->where('active', $active))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('group_id')
            ->orderBy('order_index')
            ->orderBy('id');

        return $this->paginated($query->paginate($this->perPage($request)), fn (Indicator $indicator) => $this->formatIndicator($indicator));
    }

    public function indicator(Indicator $indicator): JsonResponse
    {
        $indicator->load('group');

        return $this->success($this->formatIndicator($indicator));
    }

    // === CCM REPORTS ===

    public function ccmReports(Request $request): JsonResponse
    {
        $query = CcmReport::query()
            ->when($request->filled('jobsite'), fn (Builder $q, string $jobsite) => $q->where('jobsite', 'like', "%{$jobsite}%"))
            ->when($request->filled('date_from'), fn (Builder $q, string $date) => $q->whereDate('waktu_pelaporan', '>=', $date))
            ->when($request->filled('date_to'), fn (Builder $q, string $date) => $q->whereDate('waktu_pelaporan', '<=', $date))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('jobsite', 'like', "%{$search}%")
                        ->orWhere('nama_pelapor', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('waktu_pelaporan');

        return $this->paginated($query->paginate($this->perPage($request)), fn (CcmReport $report) => $this->formatCcm($report));
    }

    public function ccmReport(CcmReport $report): JsonResponse
    {
        return $this->success($this->formatCcm($report));
    }

    // === HIPO / NEARMISS ===

    public function hipoReports(Request $request): JsonResponse
    {
        $query = HipoReport::with(['site.company', 'user'])
            ->when($request->filled('status'), fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($request->filled('risk_level'), fn (Builder $q, string $level) => $q->where('risk_level', $level))
            ->when($request->filled('category'), fn (Builder $q, string $category) => $q->where('category', $category))
            ->when($request->filled('jenis_hipo'), fn (Builder $q, string $jenis) => $q->where('jenis_hipo', $jenis))
            ->when($request->filled('site_id'), fn (Builder $q, string $siteId) => $q->where('site_id', $siteId))
            ->when($request->filled('date_from'), fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->filled('date_to'), fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage($request)), fn (HipoReport $report) => $this->formatHipo($report));
    }

    public function hipoReport(HipoReport $report): JsonResponse
    {
        $report->load(['site.company', 'user']);

        return $this->success($this->formatHipo($report));
    }

    // === FORMS (MANDALA) ===

    public function forms(Request $request): JsonResponse
    {
        $query = Form::with(['department', 'company', 'site'])
            ->when($request->filled('department_id'), fn (Builder $q, string $id) => $q->where('department_id', $id))
            ->when($request->filled('company_id'), fn (Builder $q, string $id) => $q->where('company_id', $id))
            ->when($request->filled('doc_type'), fn (Builder $q, string $type) => $q->where('doc_type', $type))
            ->when($request->filled('type'), fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($request->filled('active'), fn (Builder $q, string $active) => $q->where('active', $active))
            ->when($request->filled('q'), function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        return $this->paginated($query->paginate($this->perPage($request)), fn (Form $form) => $this->formatForm($form));
    }

    public function form(Form $form): JsonResponse
    {
        $form->load(['department', 'company', 'site']);

        return $this->success($this->formatForm($form));
    }

    // === HELPERS ===

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 25), 1), self::MAX_PER_PAGE);
    }

    private function success(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function paginated($paginator, callable $mapper): JsonResponse
    {
        return response()->json([
            'success' => true,
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

    private function fileUrl(?string $path): ?string
    {
        return $path ? route('pubfile.stream', ['path' => $path]) : null;
    }

    // === FORMATTERS ===

    private function formatCompany(Company $company): array
    {
        return [
            'id' => $company->id,
            'code' => $company->code,
            'name' => $company->name,
            'legal_name' => $company->legal_name,
            'status' => $company->status,
            'sites' => $company->relationLoaded('sites')
                ? $company->sites->map(fn (Site $site) => [
                    'id' => $site->id,
                    'name' => $site->name,
                    'code' => $site->code,
                ])
                : null,
            'addresses' => $company->relationLoaded('addresses')
                ? $company->addresses
                : null,
        ];
    }

    private function formatGroup(IndicatorGroup $group): array
    {
        return [
            'id' => $group->id,
            'code' => $group->code,
            'name' => $group->name,
            'description' => $group->description,
            'order_index' => $group->order_index,
            'active' => $group->active,
            'indicators' => $group->relationLoaded('indicators')
                ? $group->indicators->map(fn (Indicator $indicator) => $this->formatIndicator($indicator))
                : null,
        ];
    }

    private function formatIndicator(Indicator $indicator): array
    {
        return [
            'id' => $indicator->id,
            'code' => $indicator->code,
            'name' => $indicator->name,
            'description' => $indicator->description,
            'group_id' => $indicator->group_id,
            'order_index' => $indicator->order_index,
            'active' => $indicator->active,
            'group' => $indicator->relationLoaded('group')
                ? [
                    'id' => $indicator->group->id,
                    'code' => $indicator->group->code,
                    'name' => $indicator->group->name,
                ]
                : null,
        ];
    }

    private function formatCcm(CcmReport $report): array
    {
        return [
            'id' => $report->id,
            'jobsite' => $report->jobsite,
            'nama_pelapor' => $report->nama_pelapor,
            'waktu_pelaporan' => $report->waktu_pelaporan?->toISOString(),
            'description' => $report->description,
            'status' => $report->status,
            'evidence' => $report->evidence
                ? data_get($report->evidence, 'path')
                : null,
            'evidence_url' => $this->fileUrl(data_get($report->evidence, 'path')),
        ];
    }

    private function formatHipo(HipoReport $report): array
    {
        return [
            'id' => $report->id,
            'title' => $report->title,
            'description' => $report->description,
            'status' => $report->status,
            'risk_level' => $report->risk_level,
            'category' => $report->category,
            'jenis_hipo' => $report->jenis_hipo,
            'created_at' => $report->created_at?->toISOString(),
            'site' => $report->relationLoaded('site') && $report->site
                ? [
                    'id' => $report->site->id,
                    'name' => $report->site->name,
                    'company' => $report->site->relationLoaded('company') && $report->site->company
                        ? [
                            'id' => $report->site->company->id,
                            'name' => $report->site->company->name,
                        ]
                        : null,
                ]
                : null,
            'user' => $report->relationLoaded('user') && $report->user
                ? [
                    'id' => $report->user->id,
                    'name' => $report->user->name,
                ]
                : null,
            'evidence' => $report->evidence
                ? data_get($report->evidence, 'path')
                : null,
            'evidence_url' => $this->fileUrl(data_get($report->evidence, 'path')),
        ];
    }

    private function formatForm(Form $form): array
    {
        return [
            'id' => $form->id,
            'code' => $form->code,
            'title' => $form->title,
            'doc_type' => $form->doc_type,
            'type' => $form->type,
            'active' => $form->active,
            'department_id' => $form->department_id,
            'company_id' => $form->company_id,
            'site_id' => $form->site_id,
            'department' => $form->relationLoaded('department') && $form->department
                ? [
                    'id' => $form->department->id,
                    'name' => $form->department->name,
                ]
                : null,
            'company' => $form->relationLoaded('company') && $form->company
                ? [
                    'id' => $form->company->id,
                    'name' => $form->company->name,
                ]
                : null,
            'site' => $form->relationLoaded('site') && $form->site
                ? [
                    'id' => $form->site->id,
                    'name' => $form->site->name,
                ]
                : null,
        ];
    }
}
