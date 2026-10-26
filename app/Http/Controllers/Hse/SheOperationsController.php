<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\SheManhour;
use App\Models\SheKpiTarget;
use App\Models\SheSapTarget;
use App\Models\SheNotification;
use App\Models\Site;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Models\HseInspection;
use App\Models\HipoReport;
use App\Models\HseCorrectiveAction;
use App\Models\HseRiskAssessment;
use App\Models\HseIncident;
use App\Models\HseAudit;
use App\Models\HseFinding;
use App\Models\HseTrainingCourse;
use App\Models\HseTrainingRecord;
use App\Models\HseEnvironmentalRecord;
use App\Models\HseKpiTarget;
use App\Models\CcmReport;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SheOperationsController extends Controller
{
    public function __construct(private readonly HseDashboardService $dashboardService)
    {
        $this->middleware(['auth']);
    }

    /* =========================================================
     * 1. MANHOURS MANAGEMENT
     * ========================================================= */

    public function manhoursIndex(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SheManhour::with(['site:id,code,name', 'company:id,name', 'department:id,name', 'submitter:id,name', 'validator:id,name'])
            ->latest('date');

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $records = $query->paginate(20)->withQueryString();

        $stats = [
            'total_hours' => (float) (clone $query)->where('status', 'Validated')->sum('manhours'),
            'pending_validation' => (clone $query)->where('status', 'Submitted')->count(),
            'total_records' => (clone $query)->count(),
        ];

        return view('hse.operations.manhours', compact('filters', 'options', 'records', 'stats'));
    }

    public function manhoursCreate(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        return view('hse.operations.manhours_create', compact('filters', 'options'));
    }

    public function manhoursStore(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'site_id' => ['required', 'exists:sites,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_count' => ['required', 'integer', 'min:0'],
            'manhours' => ['required', 'numeric', 'min:0'],
            'lost_hours' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['submitted_by'] = $request->user()?->id;
        $data['status'] = 'Submitted';

        SheManhour::create($data);

        return redirect()->route('hse.dashboard.manhours.index')->with('ok', 'Data Manhours berhasil disimpan.');
    }

    public function manhoursValidate(Request $request, SheManhour $manhour)
    {
        $user = $request->user();
        if (!$user?->isAdmin()) {
            abort(403, 'Hanya Administrator atau SHE Officer yang berhak memvalidasi manhours.');
        }

        $action = $request->input('action', 'validate');

        if ($action === 'return') {
            $manhour->status = 'Returned';
            $manhour->notes = $request->input('notes', 'Dikembalikan oleh validator.');
            $manhour->save();

            if ($manhour->submitted_by) {
                SheNotification::notify(
                    userId: $manhour->submitted_by,
                    title: 'Input Manhours Dikembalikan',
                    message: "Input manhours untuk tanggal {$manhour->date->format('d M Y')} dikembalikan: {$manhour->notes}",
                    type: 'submission_returned',
                    priority: 'high',
                    link: route('hse.dashboard.manhours.index')
                );
            }

            return back()->with('ok', 'Input manhours berhasil dikembalikan ke submitter.');
        }

        $manhour->status = 'Validated';
        $manhour->validated_by = $user->id;
        $manhour->validated_at = now();
        $manhour->save();

        return back()->with('ok', 'Manhours berhasil divalidasi dan masuk ke perhitungan resmi KPI.');
    }

    /* =========================================================
     * 1. SHE PERFORMANCE SUBMENU MONITORING
     * ========================================================= */

    public function performanceIndicator(string $indicator, Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $titles = [
            'fatality' => 'Fatality Monitoring',
            'lti' => 'LTI (Lost Time Injury) Monitoring',
            'non-lti' => 'Injury Non-LTI Monitoring',
            'property-damage' => 'Property Damage Monitoring',
            'pdfr' => 'PDFR (Property Damage Frequency Rate) Monitoring',
            'pd-cost' => 'Property Damage Cost Monitoring',
            'environmental' => 'Environmental Accident Monitoring',
            'pak' => 'Penyakit Akibat Kerja (PAK) Monitoring',
            'kaptk' => 'KAPTK Monitoring',
            'near-miss' => 'Near Miss Monitoring',
        ];

        $title = $titles[$indicator] ?? 'Indicator Monitoring';

        // Query incidents specifically for this indicator
        $query = HseIncident::with(['site:id,code,name', 'department:id,name', 'reporter:id,name'])
            ->whereBetween('occurred_at', [$filters['from'], $filters['to']])
            ->latest('occurred_at');

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $query->where('department_id', $deptId);
        }

        match ($indicator) {
            'fatality' => $query->where('type', 'Fatality'),
            'lti' => $query->where('type', 'Lost Time Injury'),
            'non-lti' => $query->whereIn('type', ['Restricted Work Case', 'Medical Treatment Case', 'First Aid Case']),
            'property-damage', 'pdfr', 'pd-cost' => $query->where('type', 'Property Damage'),
            'environmental' => $query->where('type', 'Environmental'),
            'pak' => $query->where('type', 'PAK'),
            'kaptk' => $query->where('type', 'KAPTK'),
            'near-miss' => $query->where(function ($b) {
                $b->where('category', 'Near Miss')->orWhere('type', 'Near Miss');
            }),
            default => null,
        };

        $records = $query->paginate(25)->withQueryString();

        // Calculate actual & target comparison
        $actualCount = (clone $query)->count();
        $target = $this->dashboardService->resolveTargetValue(str_replace('-', '_', $indicator), $filters, 0);

        return view('hse.operations.indicator_detail', compact('filters', 'options', 'records', 'indicator', 'title', 'actualCount', 'target'));
    }

    public function manhoursImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'site_id' => ['nullable', 'exists:sites,id'],
        ]);

        $defaultSiteId = $request->filled('site_id') ? (int) $request->input('site_id') : null;
        $user = $request->user();

        if ($defaultSiteId && !$user?->hasSiteAccess($defaultSiteId)) {
            abort(403, 'Anda tidak memiliki hak akses ke Site ID yang dipilih.');
        }

        $file = $request->file('file');
        $path = $file->getRealPath();

        // Detect delimiter: comma, semicolon, tab
        $firstLine = file_exists($path) ? fgets(fopen($path, 'r')) : '';
        $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, "\t") ? "\t" : ',');

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($path, 'r')) !== false) {
                $header = fgetcsv($handle, 1000, $delimiter);
                $rowNum = 1;

                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    $rowNum++;
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Columns: Date, [Site Code / Site ID], Employee Count, Manhours, [Lost Hours], [Notes]
                    $dateRaw = trim($row[0] ?? '');
                    if (empty($dateRaw)) {
                        $errors[] = "Baris {$rowNum}: Tanggal kosong.";
                        continue;
                    }

                    try {
                        $date = \Carbon\Carbon::parse($dateRaw)->toDateString();
                    } catch (\Throwable $e) {
                        $errors[] = "Baris {$rowNum}: Format tanggal '{$dateRaw}' tidak valid.";
                        continue;
                    }

                    $siteId = $defaultSiteId;
                    $empIdx = 1;
                    $manhoursIdx = 2;
                    $lostIdx = 3;
                    $notesIdx = 4;

                    // If column 1 is string representing site code / name
                    if (isset($row[1]) && !is_numeric($row[1])) {
                        $siteKey = trim($row[1]);
                        $foundSite = Site::where('code', $siteKey)->orWhere('name', $siteKey)->first();
                        if ($foundSite) {
                            $siteId = $foundSite->id;
                            $empIdx = 2;
                            $manhoursIdx = 3;
                            $lostIdx = 4;
                            $notesIdx = 5;
                        }
                    }

                    if (!$siteId) {
                        $errors[] = "Baris {$rowNum}: Site wajib ditentukan via dropdown atau kolom file.";
                        continue;
                    }

                    if (!$user?->hasSiteAccess($siteId)) {
                        $errors[] = "Baris {$rowNum}: Anda tidak memiliki akses ke Site ID {$siteId}.";
                        continue;
                    }

                    $empCount = isset($row[$empIdx]) ? max(0, (int) $row[$empIdx]) : 0;
                    $manhours = isset($row[$manhoursIdx]) ? max(0, (float) str_replace(',', '.', $row[$manhoursIdx])) : 0;
                    $lostHours = isset($row[$lostIdx]) ? max(0, (float) str_replace(',', '.', $row[$lostIdx])) : 0;
                    $notes = isset($row[$notesIdx]) ? trim($row[$notesIdx]) : 'Imported via CSV';

                    // Prevent duplicate submission on same date and site
                    $exists = SheManhour::where('date', $date)
                        ->where('site_id', $siteId)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    SheManhour::create([
                        'date' => $date,
                        'site_id' => $siteId,
                        'employee_count' => $empCount,
                        'manhours' => $manhours,
                        'lost_hours' => $lostHours,
                        'notes' => $notes,
                        'submitted_by' => $user?->id,
                        'status' => 'Submitted',
                    ]);
                    $imported++;
                }
                fclose($handle);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file import: ' . $e->getMessage());
        }

        $msg = "Hasil Import Manhours: {$imported} entri berhasil ditambahkan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} entri duplikat dilewati.";
        }
        if (!empty($errors)) {
            $msg .= ' ' . count($errors) . ' baris bermasalah: ' . implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with($imported > 0 ? 'ok' : 'warning', $msg);
    }

    /* =========================================================
     * 2. KPI TARGET MANAGEMENT
     * ========================================================= */

    public function targetsIndex(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $year = (int) $request->input('year', now()->year);

        $targets = SheKpiTarget::with('site:id,code,name')
            ->where('year', $year)
            ->get();

        $kpiKeys = [
            'fatality' => ['label' => 'Total Fatality', 'unit' => 'kasus', 'direction' => 'lower'],
            'lti' => ['label' => 'Total LTI', 'unit' => 'kasus', 'direction' => 'lower'],
            'ltifr' => ['label' => 'LTIFR', 'unit' => 'rate', 'direction' => 'lower'],
            'ltisr' => ['label' => 'LTISR', 'unit' => 'rate', 'direction' => 'lower'],
            'injury_non_lti' => ['label' => 'Injury Non-LTI', 'unit' => 'kasus', 'direction' => 'lower'],
            'property_damage' => ['label' => 'Property Damage', 'unit' => 'kasus', 'direction' => 'lower'],
            'pdfr' => ['label' => 'PDFR', 'unit' => 'rate', 'direction' => 'lower'],
            'pd_cost' => ['label' => 'Property Damage Cost', 'unit' => 'Rp', 'direction' => 'lower'],
            'environmental' => ['label' => 'Environmental Accident', 'unit' => 'kasus', 'direction' => 'lower'],
            'pak' => ['label' => 'PAK', 'unit' => 'kasus', 'direction' => 'lower'],
            'kaptk' => ['label' => 'KAPTK', 'unit' => 'kasus', 'direction' => 'lower'],
            'near_miss' => ['label' => 'Near Miss', 'unit' => 'laporan', 'direction' => 'higher'],
            'hipo' => ['label' => 'HIPO Near Miss', 'unit' => 'laporan', 'direction' => 'lower'],
            'manhours' => ['label' => 'Manhours', 'unit' => 'jam', 'direction' => 'higher'],
        ];

        return view('hse.operations.targets', compact('filters', 'options', 'targets', 'year', 'kpiKeys'));
    }

    public function targetsCreate(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $year = (int) $request->input('year', now()->year);

        $kpiKeys = [
            'fatality' => ['label' => 'Total Fatality', 'unit' => 'kasus', 'direction' => 'lower'],
            'lti' => ['label' => 'Total LTI', 'unit' => 'kasus', 'direction' => 'lower'],
            'ltifr' => ['label' => 'LTIFR', 'unit' => 'rate', 'direction' => 'lower'],
            'ltisr' => ['label' => 'LTISR', 'unit' => 'rate', 'direction' => 'lower'],
            'injury_non_lti' => ['label' => 'Injury Non-LTI', 'unit' => 'kasus', 'direction' => 'lower'],
            'property_damage' => ['label' => 'Property Damage', 'unit' => 'kasus', 'direction' => 'lower'],
            'pdfr' => ['label' => 'PDFR', 'unit' => 'rate', 'direction' => 'lower'],
            'pd_cost' => ['label' => 'Property Damage Cost', 'unit' => 'Rp', 'direction' => 'lower'],
            'environmental' => ['label' => 'Environmental Accident', 'unit' => 'kasus', 'direction' => 'lower'],
            'pak' => ['label' => 'PAK', 'unit' => 'kasus', 'direction' => 'lower'],
            'kaptk' => ['label' => 'KAPTK', 'unit' => 'kasus', 'direction' => 'lower'],
            'near_miss' => ['label' => 'Near Miss', 'unit' => 'laporan', 'direction' => 'higher'],
            'hipo' => ['label' => 'HIPO Near Miss', 'unit' => 'laporan', 'direction' => 'lower'],
            'manhours' => ['label' => 'Manhours', 'unit' => 'jam', 'direction' => 'higher'],
        ];

        return view('hse.operations.targets_create', compact('filters', 'options', 'year', 'kpiKeys'));
    }

    public function targetsStore(Request $request)
    {
        $data = $request->validate([
            'kpi_key' => ['required', 'string'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'year' => ['required', 'integer'],
            'target_value' => ['required', 'numeric'],
            'unit' => ['nullable', 'string'],
            'direction' => ['required', 'in:lower,higher'],
        ]);

        $data['created_by'] = $request->user()?->id;

        // 1. Update or create in SheKpiTarget
        SheKpiTarget::updateOrCreate(
            [
                'kpi_key' => $data['kpi_key'],
                'site_id' => $data['site_id'] ?: null,
                'year' => $data['year'],
                'month' => null,
            ],
            $data
        );

        // 2. Synchronize to HseKpiTarget
        HseKpiTarget::updateOrCreate(
            ['key' => $data['kpi_key']],
            [
                'label' => ucwords(str_replace('_', ' ', $data['kpi_key'])),
                'target_value' => $data['target_value'],
                'unit' => $data['unit'] ?? null,
                'direction' => $data['direction'] === 'lower' ? 'down' : 'up',
                'is_active' => true,
            ]
        );

        return redirect()->route('hse.dashboard.targets.index')->with('ok', 'Target KPI berhasil disimpan dan disinkronkan ke seluruh sistem.');
    }

    /* =========================================================
     * 3. SAFETY ACCOUNTABILITY PROGRAM (SAP)
     * ========================================================= */

    public function sapDashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        // Actual achievements from models - ensure inspections do NOT double-count PTO
        $inspQuery = HseInspection::whereBetween('completed_date', [$filters['from'], $filters['to']])
            ->where('type', '!=', 'PTO')
            ->where('status', 'Completed');
        $ptoQuery = HseInspection::whereBetween('completed_date', [$filters['from'], $filters['to']])
            ->where('type', 'PTO')
            ->where('status', 'Completed');
        $hazardQuery = HipoReport::whereBetween('report_time', [$filters['from'], $filters['to']]);

        if ($filters['scope_site_ids'] !== null) {
            $inspQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
            $ptoQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
            $hazardQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $inspQuery->where('department_id', $deptId);
            $ptoQuery->where('department_id', $deptId);
            $hazardQuery->whereHas('user', fn ($u) => $u->where('department_id', $deptId));
        }

        $inspectionsCount = $inspQuery->count();
        $ptoCount = $ptoQuery->count();
        $hazardCount = $hazardQuery->count();

        // Targets: scoped by year, month, and site if available
        $targetQuery = SheSapTarget::where('year', $filters['year']);
        if ($filters['period'] === 'monthly') {
            $targetQuery->where('month', $filters['month']);
        }
        if ($filters['scope_site_ids'] !== null) {
            $targetQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($deptId)) {
            $targetQuery->where('department_id', $deptId);
        }

        $targetInsp = (int) (clone $targetQuery)->sum('target_inspection');
        $targetPto = (int) (clone $targetQuery)->sum('target_pto');
        $targetHazard = (int) (clone $targetQuery)->sum('target_hazard');

        $totalTarget = $targetInsp + $targetPto + $targetHazard;
        $totalActual = $inspectionsCount + $ptoCount + $hazardCount;
        $achievementRate = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : ($totalActual > 0 ? 100.0 : 0.0);

        // Participation & Overdue
        $activeEmployees = User::where('is_active', true)->count();
        $hipoPartQuery = HipoReport::whereBetween('report_time', [$filters['from'], $filters['to']]);
        if ($filters['scope_site_ids'] !== null) {
            $hipoPartQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        $participatingUsers = $hipoPartQuery->distinct('user_id')->count('user_id');
        $employeeParticipation = $activeEmployees > 0 ? round(($participatingUsers / $activeEmployees) * 100, 1) : 0;

        $overdueQuery = HseInspection::where('status', 'Scheduled')
            ->whereDate('scheduled_date', '<', now()->toDateString());
        if ($filters['scope_site_ids'] !== null) {
            $overdueQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        $overdueActivities = $overdueQuery->count();

        $kpis = [
            ['label' => 'Total Target SAP', 'value' => $totalTarget, 'unit' => 'kegiatan'],
            ['label' => 'Total Actual SAP', 'value' => $totalActual, 'unit' => 'kegiatan'],
            ['label' => 'Achievement %', 'value' => $achievementRate, 'unit' => '%'],
            ['label' => 'Inspection Completed', 'value' => $inspectionsCount, 'unit' => 'inspeksi'],
            ['label' => 'PTO Completed', 'value' => $ptoCount, 'unit' => 'observasi'],
            ['label' => 'Hazard Report Submitted', 'value' => $hazardCount, 'unit' => 'laporan'],
            ['label' => 'Employee Participation', 'value' => $employeeParticipation, 'unit' => '%'],
            ['label' => 'Overdue SAP Activities', 'value' => $overdueActivities, 'unit' => 'kegiatan'],
        ];

        // Monthly trends
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $year = (int) $filters['year'];
        $actualMonthly = [];
        $targetMonthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $c1 = HseInspection::whereYear('completed_date', $year)->whereMonth('completed_date', $m)->count();
            $c2 = HipoReport::whereYear('report_time', $year)->whereMonth('report_time', $m)->count();
            $actualMonthly[] = $c1 + $c2;

            $monthTarget = (int) SheSapTarget::where('year', $year)->where('month', $m)->selectRaw('sum(target_inspection + target_pto + target_hazard) as t')->value('t');
            $targetMonthly[] = $monthTarget > 0 ? $monthTarget : 0;
        }

        // Real Department Ranking based on actual SAP activities
        $deptActivityCounts = [];
        $departments = Department::orderBy('name')->get(['id', 'name']);
        foreach ($departments as $dept) {
            $inspCount = HseInspection::where('department_id', $dept->id)
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();
            $hazardCountDept = HipoReport::whereHas('user', fn ($u) => $u->where('department_id', $dept->id))
                ->whereBetween('report_time', [$filters['from'], $filters['to']])
                ->count();
            $deptActivityCounts[$dept->name] = $inspCount + $hazardCountDept;
        }
        arsort($deptActivityCounts);
        $topDepts = array_slice($deptActivityCounts, 0, 6, true);

        // Real Site Comparison based on actual SAP activities
        $siteActivityCounts = [];
        $sites = Site::orderBy('name')->get(['id', 'name', 'code']);
        foreach ($sites as $st) {
            $sInsp = HseInspection::where('site_id', $st->id)
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();
            $sHazard = HipoReport::where('site_id', $st->id)
                ->whereBetween('report_time', [$filters['from'], $filters['to']])
                ->count();
            $siteActivityCounts[$st->code ?: $st->name] = $sInsp + $sHazard;
        }
        arsort($siteActivityCounts);
        $topSites = array_slice($siteActivityCounts, 0, 6, true);

        $charts = [
            'actual_vs_target' => [
                'labels' => $months,
                'series' => [
                    ['name' => 'Actual', 'data' => $actualMonthly, 'color' => '#1646A0'],
                    ['name' => 'Target', 'data' => $targetMonthly, 'color' => '#F59E0B'],
                ],
            ],
            'department_ranking' => [
                'labels' => array_keys($topDepts),
                'data' => array_values($topDepts),
            ],
            'site_comparison' => [
                'labels' => array_keys($topSites),
                'data' => array_values($topSites),
            ],
        ];

        return view('hse.operations.sap', compact(
            'filters', 'options', 'kpis', 'inspectionsCount', 'ptoCount', 'hazardCount',
            'targetInsp', 'targetPto', 'targetHazard', 'charts', 'employeeParticipation', 'overdueActivities'
        ));
    }

    public function sapAchievement(string $type, Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $types = [
            'inspection' => ['title' => 'Inspection Achievement', 'model' => 'HseInspection'],
            'pto' => ['title' => 'PTO Achievement', 'model' => 'HseInspection'],
            'hazard' => ['title' => 'Hazard Report Achievement', 'model' => 'HipoReport'],
        ];

        $meta = $types[$type] ?? $types['inspection'];
        $title = $meta['title'];

        if ($type === 'hazard') {
            $hazardQuery = HipoReport::with(['user:id,name', 'site:id,name'])
                ->whereBetween('report_time', [$filters['from'], $filters['to']]);
            if ($filters['scope_site_ids'] !== null) {
                $hazardQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
            }
            $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
            if (!empty($deptId)) {
                $hazardQuery->whereHas('user', fn ($u) => $u->where('department_id', $deptId));
            }
            $records = $hazardQuery->latest('report_time')->paginate(25);
        } else {
            $query = HseInspection::with(['inspector:id,name', 'site:id,name'])
                ->whereBetween('scheduled_date', [$filters['from'], $filters['to']]);
            if ($filters['scope_site_ids'] !== null) {
                $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
            }
            $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
            if (!empty($deptId)) {
                $query->where('department_id', $deptId);
            }
            if ($type === 'pto') {
                $query->where('type', 'PTO');
            } else {
                $query->where('type', '!=', 'PTO');
            }
            $records = $query->latest('scheduled_date')->paginate(25);
        }

        return view('hse.operations.sap_achievement', compact('filters', 'options', 'records', 'type', 'title'));
    }

    public function sapEmployeePerformance(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $empQuery = User::where('is_active', true)
            ->with(['department:id,name', 'sites:id,name,code']);

        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $empQuery->where('department_id', $deptId);
        }
        if ($filters['scope_site_ids'] !== null) {
            $empQuery->whereHas('sites', fn ($s) => $s->whereIn('sites.id', $filters['scope_site_ids'] ?: [-1]));
        }

        $employees = $empQuery->paginate(25);

        // Calculate actual performance metrics per employee for the period
        $stats = [];
        foreach ($employees as $emp) {
            $actualInsp = HseInspection::where('inspector_id', $emp->id)
                ->where('type', '!=', 'PTO')
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();

            $actualPto = HseInspection::where('inspector_id', $emp->id)
                ->where('type', 'PTO')
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();

            $actualHazard = HipoReport::where('user_id', $emp->id)
                ->whereBetween('report_time', [$filters['from'], $filters['to']])
                ->count();

            $totalActual = $actualInsp + $actualPto + $actualHazard;

            // Target from SheSapTarget
            $userTarget = SheSapTarget::where('user_id', $emp->id)
                ->where('year', $filters['year'])
                ->when($filters['period'] === 'monthly', fn ($q) => $q->where('month', $filters['month']))
                ->selectRaw('sum(target_inspection + target_pto + target_hazard) as t')
                ->value('t');

            $target = $userTarget !== null && $userTarget > 0 ? (int) $userTarget : null;

            $achievementPct = null;
            if ($target !== null && $target > 0) {
                $achievementPct = min(100, round(($totalActual / $target) * 100));
            } elseif ($totalActual > 0) {
                $achievementPct = 100;
            }

            $stats[$emp->id] = [
                'actual_insp' => $actualInsp,
                'actual_pto' => $actualPto,
                'actual_hazard' => $actualHazard,
                'total_actual' => $totalActual,
                'target' => $target,
                'achievement_pct' => $achievementPct,
            ];
        }

        return view('hse.operations.sap_employee', compact('filters', 'options', 'employees', 'stats'));
    }

    public function sapDepartmentPerformance(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $deptQuery = Department::withCount('users');
        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $deptQuery->where('id', $deptId);
        }
        $departments = $deptQuery->get();

        $rows = [];
        foreach ($departments as $dept) {
            $actualInsp = HseInspection::where('department_id', $dept->id)
                ->where('type', '!=', 'PTO')
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();

            $actualPto = HseInspection::where('department_id', $dept->id)
                ->where('type', 'PTO')
                ->whereBetween('completed_date', [$filters['from'], $filters['to']])
                ->where('status', 'Completed')
                ->count();

            $actualHazard = HipoReport::whereHas('user', fn ($u) => $u->where('department_id', $dept->id))
                ->whereBetween('report_time', [$filters['from'], $filters['to']])
                ->count();

            $totalActual = $actualInsp + $actualPto + $actualHazard;

            $deptTarget = SheSapTarget::where('department_id', $dept->id)
                ->where('year', $filters['year'])
                ->when($filters['period'] === 'monthly', fn ($q) => $q->where('month', $filters['month']))
                ->selectRaw('sum(target_inspection + target_pto + target_hazard) as t')
                ->value('t');

            $target = $deptTarget !== null && $deptTarget > 0 ? (int) $deptTarget : 0;
            $achievementRate = $target > 0 ? round(($totalActual / $target) * 100, 1) : null;

            $statusLabel = 'Belum Ada Target';
            $statusColor = 'bg-slate-100 text-slate-700';
            if ($achievementRate !== null) {
                if ($achievementRate >= 90) {
                    $statusLabel = 'Sangat Baik';
                    $statusColor = 'bg-emerald-100 text-emerald-800';
                } elseif ($achievementRate >= 75) {
                    $statusLabel = 'Baik';
                    $statusColor = 'bg-blue-100 text-blue-800';
                } elseif ($achievementRate >= 50) {
                    $statusLabel = 'Cukup';
                    $statusColor = 'bg-amber-100 text-amber-800';
                } else {
                    $statusLabel = 'Perlu Perhatian';
                    $statusColor = 'bg-rose-100 text-rose-800';
                }
            }

            $rows[] = [
                'department' => $dept,
                'actual_insp' => $actualInsp,
                'actual_pto' => $actualPto,
                'actual_hazard' => $actualHazard,
                'total_actual' => $totalActual,
                'target' => $target,
                'achievement_rate' => $achievementRate,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
            ];
        }

        // Sort descending by achievement rate or total actual
        usort($rows, function ($a, $b) {
            $rateA = $a['achievement_rate'] ?? ($a['total_actual'] > 0 ? 50 : 0);
            $rateB = $b['achievement_rate'] ?? ($b['total_actual'] > 0 ? 50 : 0);
            if ($rateA == $rateB) {
                return $b['total_actual'] <=> $a['total_actual'];
            }
            return $rateB <=> $rateA;
        });

        return view('hse.operations.sap_department', compact('filters', 'options', 'rows'));
    }

    public function sapTargetsIndex(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $targets = SheSapTarget::with(['user:id,name', 'department:id,name', 'site:id,name'])
            ->latest()
            ->paginate(20);

        return view('hse.operations.sap_targets', compact('filters', 'options', 'targets'));
    }

    public function sapTargetsCreate(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        return view('hse.operations.sap_targets_create', compact('filters', 'options'));
    }

    public function sapTargetsStore(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'target_inspection' => ['required', 'integer', 'min:0'],
            'target_pto' => ['required', 'integer', 'min:0'],
            'target_hazard' => ['required', 'integer', 'min:0'],
        ]);

        $data['created_by'] = $request->user()?->id;

        // Upsert to prevent duplicate targets
        SheSapTarget::updateOrCreate(
            [
                'user_id' => $data['user_id'] ?: null,
                'department_id' => $data['department_id'] ?: null,
                'site_id' => $data['site_id'] ?: null,
                'year' => $data['year'],
                'month' => $data['month'],
            ],
            $data
        );

        return redirect()->route('hse.dashboard.sap.targets.index')->with('ok', 'Target SAP berhasil disimpan.');
    }

    /* =========================================================
     * 4. NOTIFICATIONS
     * ========================================================= */

    public function notificationsIndex(Request $request)
    {
        $user = $request->user();

        // Run automated checks for PICA H-3, H0, Overdue, and Document Review Due
        SheNotification::generateAutomatedReminders();

        $notifications = SheNotification::where('user_id', $user->id)
            ->latest()
            ->paginate(30);

        $unreadCount = SheNotification::where('user_id', $user->id)->where('is_read', false)->count();

        return view('hse.operations.notifications', compact('notifications', 'unreadCount'));
    }

    public function notificationsMarkRead(Request $request, SheNotification $notification)
    {
        if ($notification->user_id === $request->user()?->id) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }

        return back()->with('ok', 'Notifikasi telah ditandai sudah dibaca.');
    }

    public function notificationsMarkAllRead(Request $request)
    {
        SheNotification::where('user_id', $request->user()?->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('ok', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /* =========================================================
     * 5. PICA VERIFICATION & UPDATE
     * ========================================================= */

    public function picaUpdate(Request $request, HseCorrectiveAction $pica)
    {
        $user = $request->user();

        // Site access check
        if ($pica->site_id && !$user?->hasSiteAccess($pica->site_id)) {
            abort(403, 'Anda tidak memiliki hak akses ke site PICA ini.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,Waiting Verification,Pending Verification,Closed'],
            'action_note' => ['nullable', 'string', 'max:1000'],
            'evidence_file' => ['nullable', 'file', 'max:10240'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('pica_evidence', 'public');
            $pica->evidence_path = $path;
        }

        if ($request->filled('assigned_to')) {
            $pica->assigned_to = $request->input('assigned_to');
        }

        if ($request->filled('due_date')) {
            $pica->due_date = $request->input('due_date');
        }

        if ($request->filled('action_note')) {
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - " . ($user?->name ?? 'User') . "]: " . $request->input('action_note'));
        }

        // Strict State Transition Guard: Closed CANNOT be bypassed directly
        if ($data['status'] === 'Closed') {
            // Verify permission: Must be Admin, SHE Manager, or assigned Verifier
            $canClose = $user?->isAdmin() || ($pica->verified_by && $pica->verified_by == $user?->id);
            if (!$canClose) {
                return back()->with('error', 'Penutupan PICA (Closed) hanya dapat disahkan melalui verifikasi resmi oleh verifier atau administrator.');
            }

            if (empty($pica->evidence_path) && !$request->hasFile('evidence_file')) {
                return back()->with('error', 'PICA tidak dapat ditutup (Closed) tanpa adanya file bukti (evidence).');
            }

            $pica->status = 'Closed';
            $pica->closed_at = now();
            $pica->verified_by = $user->id;
            $pica->verified_at = now();
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - CLOSED by " . ($user?->name ?? 'Verifier') . "]");
        } elseif ($data['status'] === 'Waiting Verification' || $data['status'] === 'Pending Verification') {
            if (empty($pica->evidence_path) && !$request->hasFile('evidence_file')) {
                return back()->with('error', 'Harap lampirkan file bukti pelaksanaan sebelum mengajukan verifikasi.');
            }
            $pica->status = 'Pending Verification';
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - SUBMITTED FOR VERIFICATION by " . ($user?->name ?? 'User') . "]");

            // Notify verifier / admins
            if ($pica->verified_by) {
                SheNotification::notify(
                    userId: $pica->verified_by,
                    title: 'PICA Menunggu Verifikasi: ' . $pica->action_no,
                    message: "PICA {$pica->action_no} telah diajukan untuk verifikasi penyelesaian oleh {$user?->name}.",
                    type: 'verification_request',
                    priority: 'high',
                    link: route('hse.dashboard.pica.detail', $pica->id)
                );
            }
        } elseif ($data['status'] === 'In Progress' && $request->filled('rejection_reason')) {
            $pica->status = 'In Progress';
            $pica->rejection_reason = $request->input('rejection_reason');
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - REJECTED by " . ($user?->name ?? 'User') . "]: " . $request->input('rejection_reason'));
        } else {
            $pica->status = $data['status'];
        }

        $pica->save();

        return back()->with('ok', 'Status PICA #' . $pica->action_no . ' berhasil diperbarui.');
    }

    /* =========================================================
     * 6. RISK REGISTER (FASE 3)
     * ========================================================= */

    public function riskDashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        // Core Risk Assessments
        $riskQuery = HseRiskAssessment::query()->forSites($filters['scope_site_ids']);
        if ($filters['site_id']) {
            $riskQuery->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $riskQuery->where('department_id', $filters['dept_id']);
        }

        $allRisks = (clone $riskQuery)->get();
        $totalRisks = $allRisks->count();
        $lowRisks = $allRisks->where('risk_level', 'Low')->count();
        $mediumRisks = $allRisks->where('risk_level', 'Medium')->count();
        $highRisks = $allRisks->where('risk_level', 'High')->count();
        $extremeRisks = $allRisks->where('risk_level', 'Extreme')->count();

        // Matrix 5x5
        $matrix = [];
        for ($l = 5; $l >= 1; $l--) {
            for ($c = 1; $c <= 5; $c++) {
                $matrix[$l][$c] = 0;
            }
        }
        foreach ($allRisks as $r) {
            $l = max(1, min(5, (int) ($r->residual_likelihood ?: $r->likelihood ?: 1)));
            $c = max(1, min(5, (int) ($r->residual_consequence ?: $r->consequence ?: 1)));
            $matrix[$l][$c]++;
        }

        // PICA / Risk Controls KPIs
        $controlsQuery = HseCorrectiveAction::query();
        if ($filters['scope_site_ids'] !== null) {
            $controlsQuery->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $controlsQuery->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $controlsQuery->where('department_id', $filters['dept_id']);
        }

        $openTreatments = (clone $controlsQuery)->whereIn('status', ['Open', 'In Progress'])->count();
        $completedControls = (clone $controlsQuery)->where('status', 'Closed')->count();
        $overdueControls = (clone $controlsQuery)
            ->whereNotIn('status', ['Closed'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        // High & Extreme Risks Table
        $highRiskRows = (clone $riskQuery)
            ->with(['site:id,name,code', 'department:id,name', 'owner:id,name'])
            ->whereIn('risk_level', ['High', 'Extreme'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent Controls
        $recentControls = (clone $controlsQuery)
            ->with(['site:id,name,code', 'department:id,name', 'assignee:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        // Users & Depts for quick modal
        $users = User::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.risk_dashboard', compact(
            'filters', 'options', 'totalRisks', 'lowRisks', 'mediumRisks', 'highRisks', 'extremeRisks',
            'openTreatments', 'completedControls', 'overdueControls', 'matrix', 'highRiskRows',
            'recentControls', 'users', 'departments'
        ));
    }

    public function riskList(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = HseRiskAssessment::with(['site:id,name,code', 'department:id,name', 'owner:id,name'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $query->where('department_id', $filters['dept_id']);
        }
        if ($request->filled('level')) {
            $query->where('risk_level', $request->input('level'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($b) use ($q) {
                $b->where('activity', 'like', "%{$q}%")
                  ->orWhere('hazard', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('existing_controls', 'like', "%{$q}%");
            });
        }
        if ($request->filled('likelihood') && $request->filled('consequence')) {
            $query->where('residual_likelihood', (int) $request->input('likelihood'))
                  ->where('residual_consequence', (int) $request->input('consequence'));
        }

        $risks = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.risk_list', compact('filters', 'options', 'risks', 'users', 'departments'));
    }

    public function riskHighActivities(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        // High risk items from both HseRiskAssessment (High & Extreme) and CCM critical activity categories
        $query = HseRiskAssessment::with(['site:id,name,code', 'department:id,name', 'owner:id,name'])
            ->whereIn('risk_level', ['High', 'Extreme'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $query->where('department_id', $filters['dept_id']);
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($b) use ($q) {
                $b->where('activity', 'like', "%{$q}%")
                  ->orWhere('hazard', 'like', "%{$q}%");
            });
        }

        $activities = $query->paginate(20)->withQueryString();

        // Also fetch CCM recent critical reports for context
        $ccmReports = CcmReport::latest('waktu_pelaporan')->limit(15)->get();

        return view('hse.operations.risk_high_activities', compact('filters', 'options', 'activities', 'ccmReports'));
    }

    public function riskAssessmentStore(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'activity' => ['required', 'string', 'max:255'],
            'hazard' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'existing_controls' => ['nullable', 'string', 'max:1000'],
            'likelihood' => ['required', 'integer', 'min:1', 'max:5'],
            'consequence' => ['required', 'integer', 'min:1', 'max:5'],
            'residual_likelihood' => ['nullable', 'integer', 'min:1', 'max:5'],
            'residual_consequence' => ['nullable', 'integer', 'min:1', 'max:5'],
            'treatment' => ['nullable', 'string', 'max:1000'],
            'treatment_due_date' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'exists:users,id'],
        ]);

        $l = (int) $data['likelihood'];
        $c = (int) $data['consequence'];
        $inherentScore = $l * $c;

        $resL = (int) ($data['residual_likelihood'] ?? $l);
        $resC = (int) ($data['residual_consequence'] ?? $c);
        $residualScore = $resL * $resC;

        $riskLevel = HseRiskAssessment::levelForScore($inherentScore);

        $assessment = HseRiskAssessment::create([
            'site_id' => $data['site_id'],
            'department_id' => $data['department_id'],
            'owner_id' => $data['owner_id'] ?? $request->user()?->id,
            'activity' => $data['activity'],
            'hazard' => $data['hazard'],
            'category' => $data['category'],
            'existing_controls' => $data['existing_controls'] ?? null,
            'likelihood' => $l,
            'consequence' => $c,
            'inherent_score' => $inherentScore,
            'residual_likelihood' => $resL,
            'residual_consequence' => $resC,
            'residual_score' => $residualScore,
            'risk_level' => $riskLevel,
            'treatment' => $data['treatment'] ?? null,
            'treatment_due_date' => $data['treatment_due_date'] ?? null,
            'treatment_status' => !empty($data['treatment']) ? 'In Progress' : 'Not Required',
            'status' => 'Active',
        ]);

        // Auto create PICA if High or Extreme and treatment specified
        if (in_array($riskLevel, ['High', 'Extreme']) && !empty($data['treatment'])) {
            $picaActionNo = 'PICA-RSK-' . $assessment->id;
            HseCorrectiveAction::firstOrCreate(
                ['action_no' => $picaActionNo],
                [
                    'source_type' => HseRiskAssessment::class,
                    'source_id' => $assessment->id,
                    'source_module' => 'PICA Risk Assessment',
                    'site_id' => $data['site_id'],
                    'department_id' => $data['department_id'],
                    'assigned_to' => $data['owner_id'] ?? $request->user()?->id,
                    'title' => 'Mitigasi Risiko ' . $riskLevel . ': ' . \Illuminate\Support\Str::limit($data['activity'], 60),
                    'description' => "Bahaya: {$data['hazard']}\nKontrol yang harus diterapkan: {$data['treatment']}",
                    'priority' => $riskLevel === 'Extreme' ? 'High' : 'Medium',
                    'due_date' => $data['treatment_due_date'] ?? now()->addDays(14)->toDateString(),
                    'status' => 'Open',
                ]
            );
        }

        return back()->with('ok', 'Data Risk Assessment berhasil ditambahkan.');
    }

    public function riskControlMonitoring(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = HseCorrectiveAction::with(['site:id,name,code', 'department:id,name', 'assignee:id,name', 'verifier:id,name'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $query->where('department_id', $filters['dept_id']);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('overdue')) {
            $query->whereNotIn('status', ['Closed'])
                  ->whereNotNull('due_date')
                  ->whereDate('due_date', '<', now()->toDateString());
        }

        $controls = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.risk_control_monitoring', compact('filters', 'options', 'controls', 'users'));
    }

    /* =========================================================
     * 7. PICA MANAGEMENT (FASE 4)
     * ========================================================= */

    public function picaDashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = HseCorrectiveAction::query();
        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $query->where('department_id', $filters['dept_id']);
        }

        $allPica = (clone $query)->get();
        $totalPica = $allPica->count();
        $open = $allPica->where('status', 'Open')->count();
        $inProgress = $allPica->where('status', 'In Progress')->count();
        $waitingVerification = $allPica->where('status', 'Pending Verification')->count();
        $closed = $allPica->where('status', 'Closed')->count();
        $overdue = $allPica->filter(fn ($p) => $p->status !== 'Closed' && $p->due_date && $p->due_date < now())->count();

        $closureRate = $totalPica > 0 ? round(($closed / $totalPica) * 100, 1) : 0;

        $closedWithDates = $allPica->where('status', 'Closed')->filter(fn ($p) => $p->created_at && $p->closed_at);
        $avgDays = $closedWithDates->count() > 0
            ? round($closedWithDates->avg(fn ($p) => $p->created_at->diffInDays($p->closed_at)), 1)
            : 0;

        // Charts
        // 1. By Status
        $byStatus = [
            'labels' => ['Open', 'In Progress', 'Waiting Verification', 'Closed'],
            'data' => [$open, $inProgress, $waitingVerification, $closed],
        ];

        // 2. By Source
        $bySourceRaw = $allPica->groupBy(function ($p) {
            return $p->source_module ?: 'Other';
        })->map->count();
        $bySource = [
            'labels' => $bySourceRaw->keys()->all(),
            'data' => $bySourceRaw->values()->all(),
        ];

        // 3. By Department
        $byDeptRaw = HseCorrectiveAction::join('departments', 'departments.id', '=', 'hse_corrective_actions.department_id')
            ->selectRaw('departments.name, count(*) as count')
            ->groupBy('departments.name')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'name');
        $byDept = [
            'labels' => $byDeptRaw->keys()->all(),
            'data' => $byDeptRaw->values()->all(),
        ];

        // 4. By Site
        $bySiteRaw = HseCorrectiveAction::join('sites', 'sites.id', '=', 'hse_corrective_actions.site_id')
            ->selectRaw('sites.name, count(*) as count')
            ->groupBy('sites.name')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'name');
        $bySite = [
            'labels' => $bySiteRaw->keys()->all(),
            'data' => $bySiteRaw->values()->all(),
        ];

        // 5. Overdue Aging
        $overdueRecords = $allPica->filter(fn ($p) => $p->status !== 'Closed' && $p->due_date && $p->due_date < now());
        $aging1to7 = 0; $aging8to14 = 0; $aging15to30 = 0; $aging30plus = 0;
        foreach ($overdueRecords as $op) {
            $days = now()->diffInDays($op->due_date);
            if ($days <= 7) $aging1to7++;
            elseif ($days <= 14) $aging8to14++;
            elseif ($days <= 30) $aging15to30++;
            else $aging30plus++;
        }
        $overdueAging = [
            'labels' => ['1-7 Hari', '8-14 Hari', '15-30 Hari', '>30 Hari'],
            'data' => [$aging1to7, $aging8to14, $aging15to30, $aging30plus],
        ];

        // 6. Monthly Closure Trend (Last 6 Months)
        $months = [];
        $closedTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthObj = now()->subMonths($i);
            $key = $monthObj->format('M Y');
            $months[] = $key;
            $closedTrend[] = HseCorrectiveAction::where('status', 'Closed')
                ->whereYear('closed_at', $monthObj->year)
                ->whereMonth('closed_at', $monthObj->month)
                ->count();
        }
        $monthlyTrend = [
            'labels' => $months,
            'data' => $closedTrend,
        ];

        // 7. PIC Workload (Top 6 Assignees with open/progress)
        $workloadRaw = HseCorrectiveAction::whereNotIn('status', ['Closed'])
            ->join('users', 'users.id', '=', 'hse_corrective_actions.assigned_to')
            ->selectRaw('users.name, count(*) as count')
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->limit(6)
            ->pluck('count', 'name');
        $picWorkload = [
            'labels' => $workloadRaw->keys()->all(),
            'data' => $workloadRaw->values()->all(),
        ];

        // Recent Critical PICA
        $recentPica = (clone $query)->with(['site:id,name,code', 'department:id,name', 'assignee:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        return view('hse.operations.pica_dashboard', compact(
            'filters', 'options', 'totalPica', 'open', 'inProgress', 'waitingVerification',
            'closed', 'overdue', 'closureRate', 'avgDays', 'byStatus', 'bySource',
            'byDept', 'bySite', 'overdueAging', 'monthlyTrend', 'picWorkload', 'recentPica'
        ));
    }

    public function picaList(Request $request, ?string $source = null)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = HseCorrectiveAction::with(['site:id,name,code', 'department:id,name', 'assignee:id,name', 'verifier:id,name'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        if ($filters['dept_id']) {
            $query->where('department_id', $filters['dept_id']);
        }

        // Submenu Source Filter
        $sourceTitle = 'All PICA';
        if ($source === 'audit' || $request->input('source') === 'audit') {
            $query->where('source_module', 'like', '%Audit%');
            $sourceTitle = 'PICA Audit';
        } elseif ($source === 'investigation' || $request->input('source') === 'investigation') {
            $query->where('source_module', 'like', '%Investigation%');
            $sourceTitle = 'PICA Investigation';
        } elseif ($source === 'inspection' || $request->input('source') === 'inspection') {
            $query->where('source_module', 'like', '%Inspection%');
            $sourceTitle = 'PICA Inspection';
        } elseif ($source === 'pto' || $request->input('source') === 'pto') {
            $query->where('source_module', 'like', '%PTO%');
            $sourceTitle = 'PICA PTO';
        } elseif ($source === 'hazard' || $request->input('source') === 'hazard') {
            $query->where('source_module', 'like', '%Hazard%');
            $sourceTitle = 'PICA Hazard Report';
        } elseif ($source === 'verification' || $request->input('source') === 'verification') {
            $query->where('status', 'Pending Verification');
            $sourceTitle = 'PICA Verification Queue';
        } elseif ($source === 'overdue' || $request->input('source') === 'overdue') {
            $query->whereNotIn('status', ['Closed'])->whereDate('due_date', '<', now()->toDateString());
            $sourceTitle = 'Overdue PICA';
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($b) use ($q) {
                $b->where('action_no', 'like', "%{$q}%")
                  ->orWhere('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('root_cause', 'like', "%{$q}%")
                  ->orWhere('corrective_action', 'like', "%{$q}%");
            });
        }

        $records = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.pica_list', compact('filters', 'options', 'records', 'sourceTitle', 'source', 'users', 'departments'));
    }

    public function picaDetail(Request $request, HseCorrectiveAction $pica)
    {
        $pica->load(['site', 'department', 'assignee', 'verifier', 'source']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.pica_detail', compact('pica', 'users'));
    }

    public function picaStore(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_module' => ['required', 'string', 'max:100'],
            'site_id' => ['required', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'in:Low,Medium,High,Critical'],
            'due_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'root_cause' => ['nullable', 'string', 'max:2000'],
            'corrective_action' => ['nullable', 'string', 'max:2000'],
            'preventive_action' => ['nullable', 'string', 'max:2000'],
        ]);

        $abbr = match ($data['source_module']) {
            'PICA Audit' => 'AUD',
            'PICA Investigation' => 'INV',
            'PICA Inspection' => 'INS',
            'PICA PTO' => 'PTO',
            'PICA Hazard Report' => 'HZD',
            default => 'GEN',
        };

        $actionNo = 'PICA-' . $abbr . '-' . date('Ymd') . '-' . rand(100, 999);

        $pica = HseCorrectiveAction::create([
            'action_no' => $actionNo,
            'source_module' => $data['source_module'],
            'site_id' => $data['site_id'],
            'department_id' => $data['department_id'],
            'assigned_to' => $data['assigned_to'],
            'title' => $data['title'],
            'description' => $data['description'],
            'root_cause' => $data['root_cause'],
            'corrective_action' => $data['corrective_action'],
            'preventive_action' => $data['preventive_action'],
            'priority' => $data['priority'],
            'due_date' => $data['due_date'],
            'status' => 'Open',
            'progress_notes' => '[' . now()->toDateTimeString() . ' - ' . ($request->user()?->name ?? 'System') . ']: PICA Dibuat.',
        ]);

        if ($data['assigned_to']) {
            SheNotification::notify(
                userId: $data['assigned_to'],
                title: 'Tugas PICA Baru: ' . $pica->action_no,
                message: "Anda ditugaskan pada PICA: {$pica->title}. Due date: " . $pica->due_date->format('d M Y'),
                type: 'pica_assigned',
                priority: in_array($data['priority'], ['High', 'Critical']) ? 'high' : 'medium',
                link: route('hse.dashboard.pica.detail', $pica->id)
            );
        }

        return redirect()->route('hse.dashboard.pica.detail', $pica->id)->with('ok', 'PICA #' . $pica->action_no . ' berhasil dibuat.');
    }

    public function picaVerify(Request $request, HseCorrectiveAction $pica)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'approve') {
            $pica->status = 'Closed';
            $pica->verified_by = $request->user()?->id;
            $pica->verified_at = now();
            $pica->closed_at = now();
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - APPROVED by " . ($request->user()?->name ?? 'User') . "]: " . $data['notes']);
            $msg = 'PICA #' . $pica->action_no . ' berhasil diverifikasi dan CLOSED.';
        } else {
            $pica->status = 'In Progress';
            $pica->rejection_reason = $data['notes'];
            $notes = $pica->progress_notes ?: '';
            $pica->progress_notes = trim($notes . "\n[" . now()->toDateTimeString() . " - REJECTED by " . ($request->user()?->name ?? 'User') . "]: " . $data['notes']);
            $msg = 'Evidence PICA #' . $pica->action_no . ' ditolak dan dikembalikan ke In Progress.';

            if ($pica->assigned_to) {
                SheNotification::notify(
                    userId: $pica->assigned_to,
                    title: 'Evidence PICA Ditolak: ' . $pica->action_no,
                    message: "Evidence PICA ditolak dengan catatan: {$data['notes']}. Harap perbaiki segera.",
                    type: 'pica_rejected',
                    priority: 'high',
                    link: route('hse.dashboard.pica.detail', $pica->id)
                );
            }
        }

        $pica->save();

        return back()->with('ok', $msg);
    }

    /* =========================================================
     * 7. INCIDENT REGISTER LIFECYCLE (FASE 2)
     * ========================================================= */

    public function incidentStore(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'location' => ['required', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:100'],
            'severity' => ['required', 'in:Low,Medium,High,Critical'],
            'description' => ['required', 'string', 'max:5000'],
            'immediate_action' => ['nullable', 'string', 'max:2000'],
            'property_damage_cost' => ['nullable', 'numeric', 'min:0'],
            'lost_time_days' => ['nullable', 'integer', 'min:0'],
            'injured_name' => ['nullable', 'string', 'max:255'],
            'injury_type' => ['nullable', 'string', 'max:255'],
            'environmental_impact' => ['nullable', 'string', 'max:2000'],
            'evidence_file' => ['nullable', 'file', 'max:20480'],
        ]);

        $site = Site::findOrFail($data['site_id']);
        $year = now()->format('Y');
        $count = HseIncident::where('site_id', $site->id)->whereYear('created_at', $year)->count() + 1;
        $incidentNo = sprintf('INC-%s-%s-%04d', $site->code ?? 'SITE', $year, $count);

        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            $evidencePath = $request->file('evidence_file')->store('hse/incidents', 'public');
        }

        $recordable = in_array($data['type'], ['Fatality', 'Lost Time Injury', 'Medical Treatment Case', 'Restricted Work Case']);

        $incident = HseIncident::create([
            'incident_no' => $incidentNo,
            'site_id' => $data['site_id'],
            'department_id' => $data['department_id'] ?? null,
            'location' => $data['location'],
            'reported_by' => $request->user()?->id,
            'occurred_at' => $data['occurred_at'],
            'reported_at' => now(),
            'category' => $data['category'],
            'type' => $data['type'],
            'severity' => $data['severity'],
            'description' => $data['description'],
            'immediate_action' => $data['immediate_action'] ?? null,
            'property_damage_cost' => $data['property_damage_cost'] ?? 0,
            'lost_time_days' => $data['lost_time_days'] ?? 0,
            'recordable' => $recordable,
            'injured_name' => $data['injured_name'] ?? null,
            'injury_type' => $data['injury_type'] ?? null,
            'environmental_impact' => $data['environmental_impact'] ?? null,
            'evidence_path' => $evidencePath,
            'status' => 'Reported',
            'investigation_status' => 'Pending',
        ]);

        // Auto PICA generation for High/Critical incidents or LTI/Fatality/Property Damage
        if (in_array($data['severity'], ['High', 'Critical']) || in_array($data['type'], ['Fatality', 'Lost Time Injury', 'Property Damage', 'Environmental'])) {
            $picaActionNo = 'PICA-INC-' . $incident->id;
            HseCorrectiveAction::firstOrCreate(
                ['action_no' => $picaActionNo],
                [
                    'source_type' => HseIncident::class,
                    'source_id' => $incident->id,
                    'source_module' => 'PICA Incident',
                    'site_id' => $incident->site_id,
                    'department_id' => $incident->department_id,
                    'assigned_to' => $request->user()?->id,
                    'title' => 'Tindakan Korektif Insiden: ' . $incident->incident_no . ' (' . $incident->type . ')',
                    'description' => $incident->description,
                    'priority' => in_array($data['severity'], ['High', 'Critical']) ? 'High' : 'Medium',
                    'due_date' => now()->addDays(14)->toDateString(),
                    'status' => 'Open',
                ]
            );
        }

        // Notify SHE team
        SheNotification::notifyRole(
            role: 'admin_site',
            title: 'Laporan Insiden Baru: ' . $incident->incident_no,
            message: "Laporan insiden {$incident->type} ({$incident->severity}) di site {$site->name} telah dicatat.",
            type: 'incident_reported',
            priority: in_array($data['severity'], ['High', 'Critical']) ? 'high' : 'medium',
            link: route('hse.dashboard.incidents.show', $incident->id)
        );

        return redirect()->route('hse.dashboard.incidents.show', $incident->id)
            ->with('ok', "Insiden {$incident->incident_no} berhasil dilaporkan dan masuk ke workflow registrasi.");
    }

    public function incidentShow(HseIncident $incident)
    {
        $incident->load(['site', 'department', 'reporter', 'reviewer', 'approver', 'investigator']);
        $picas = HseCorrectiveAction::where('source_type', HseIncident::class)
            ->where('source_id', $incident->id)
            ->with(['assignee', 'verifier'])
            ->get();

        return view('hse.operations.incident_show', compact('incident', 'picas'));
    }

    public function incidentReview(Request $request, HseIncident $incident)
    {
        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident->update([
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'status' => 'Under Review',
            'investigation_status' => 'In Progress',
        ]);

        return back()->with('ok', "Insiden {$incident->incident_no} berhasil ditinjau dan status diperbarui ke Under Review.");
    }

    public function incidentInvestigate(Request $request, HseIncident $incident)
    {
        $data = $request->validate([
            'root_cause' => ['required', 'string', 'max:2000'],
            'corrective_action' => ['required', 'string', 'max:2000'],
            'preventive_action' => ['required', 'string', 'max:2000'],
            'investigation_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident->update([
            'root_cause' => $data['root_cause'],
            'corrective_action' => $data['corrective_action'],
            'preventive_action' => $data['preventive_action'],
            'investigation_notes' => $data['investigation_notes'] ?? $incident->investigation_notes,
            'investigator_id' => $request->user()?->id,
            'investigation_status' => 'Completed',
        ]);

        // Sync or update linked PICA with root cause & corrective action
        $pica = HseCorrectiveAction::where('source_type', HseIncident::class)
            ->where('source_id', $incident->id)
            ->first();
        if ($pica) {
            $pica->update([
                'root_cause' => $data['root_cause'],
                'corrective_action' => $data['corrective_action'],
                'preventive_action' => $data['preventive_action'],
            ]);
        }

        return back()->with('ok', "Investigasi untuk insiden {$incident->incident_no} berhasil diselesaikan.");
    }

    public function incidentApprove(Request $request, HseIncident $incident)
    {
        $incident->update([
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'status' => 'Approved',
        ]);

        return back()->with('ok', "Insiden {$incident->incident_no} telah disetujui secara resmi.");
    }

    public function incidentClose(Request $request, HseIncident $incident)
    {
        // Enforce: cannot close if linked PICA is not closed or verified
        $openPicas = HseCorrectiveAction::where('source_type', HseIncident::class)
            ->where('source_id', $incident->id)
            ->whereNotIn('status', ['Closed', 'Pending Verification'])
            ->count();

        if ($openPicas > 0) {
            return back()->with('error', "Insiden tidak dapat ditutup karena masih terdapat {$openPicas} tindakan perbaikan (PICA) yang belum selesai atau belum diverifikasi!");
        }

        $incident->update([
            'status' => 'Closed',
            'closed_at' => now(),
        ]);

        return back()->with('ok', "Insiden {$incident->incident_no} berhasil ditutup (Closed).");
    }

    /* =========================================================
     * 8. OPERATIONAL INSPECTION / PTO / HAZARD STORE
     * ========================================================= */

    public function inspectionStore(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'scheduled_date' => ['required', 'date'],
            'completed_date' => ['nullable', 'date'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:Scheduled,In Progress,Completed,Overdue'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'finding_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $site = Site::findOrFail($data['site_id']);
        $year = now()->format('Y');
        $prefix = $data['type'] === 'PTO' ? 'PTO' : 'INSP';
        $count = HseInspection::where('site_id', $site->id)->whereYear('created_at', $year)->count() + 1;
        $inspNo = sprintf('%s-%s-%s-%04d', $prefix, $site->code ?? 'SITE', $year, $count);

        $inspection = HseInspection::create([
            'inspection_no' => $inspNo,
            'site_id' => $data['site_id'],
            'department_id' => $data['department_id'] ?? null,
            'inspector_id' => $request->user()?->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'scheduled_date' => $data['scheduled_date'],
            'completed_date' => $data['completed_date'] ?? ($data['status'] === 'Completed' ? now()->toDateString() : null),
            'score' => $data['score'] ?? 100.0,
            'max_score' => 100.0,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        // If finding specified or score < 80, create PICA
        if (!empty($data['finding_description']) || ($data['score'] !== null && $data['score'] < 80)) {
            $desc = $data['finding_description'] ?: ('Temuan inspeksi skor rendah: ' . $data['score'] . '%. ' . ($data['notes'] ?? ''));
            HseCorrectiveAction::create([
                'action_no' => 'PICA-' . $prefix . '-' . $inspection->id,
                'source_type' => HseInspection::class,
                'source_id' => $inspection->id,
                'source_module' => 'PICA ' . $prefix,
                'site_id' => $inspection->site_id,
                'department_id' => $inspection->department_id,
                'assigned_to' => $request->user()?->id,
                'title' => 'Tindak Lanjut ' . $prefix . ': ' . $inspection->inspection_no,
                'description' => $desc,
                'priority' => 'Medium',
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'Open',
            ]);
        }

        return redirect()->route('hse.dashboard.inspections')->with('ok', "Data {$prefix} #{$inspection->inspection_no} berhasil dicatat.");
    }

    /* =========================================================
     * 9. RISK REGISTER LIFECYCLE (Review, Approve, Close)
     * ========================================================= */

    public function riskReview(Request $request, HseRiskAssessment $risk)
    {
        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $risk->update([
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'] ?? null,
            'status' => 'Reviewed',
        ]);

        return back()->with('ok', "Risk Assessment #{$risk->id} telah berhasil direview.");
    }

    public function riskApprove(Request $request, HseRiskAssessment $risk)
    {
        $risk->update([
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'status' => 'Approved',
        ]);

        return back()->with('ok', "Risk Assessment #{$risk->id} telah disetujui (Approved).");
    }

    public function riskClose(Request $request, HseRiskAssessment $risk)
    {
        $risk->update([
            'status' => 'Archived',
        ]);

        return back()->with('ok', "Risk Assessment #{$risk->id} telah diarsipkan (Archived).");
    }

    /* =========================================================
     * 10. TRAINING & ENVIRONMENTAL OPERATIONAL STORES
     * ========================================================= */

    public function trainingRecordStore(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:hse_training_courses,id'],
            'user_id' => ['required', 'exists:users,id'],
            'site_id' => ['required', 'exists:sites,id'],
            'scheduled_date' => ['required', 'date'],
            'completed_date' => ['nullable', 'date'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:Not Started,Scheduled,In Progress,Completed,Expired'],
        ]);

        HseTrainingRecord::create($data);

        return redirect()->route('hse.dashboard.training')->with('ok', 'Catatan pelatihan karyawan berhasil disimpan.');
    }

    public function environmentalRecordStore(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'recorded_date' => ['required', 'date'],
            'metric' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['recorded_by'] = $request->user()?->id;

        HseEnvironmentalRecord::create($data);

        return redirect()->route('hse.dashboard.environment')->with('ok', 'Data pencatatan lingkungan berhasil ditambahkan.');
    }
}



