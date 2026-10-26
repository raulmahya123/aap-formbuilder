<?php

namespace App\Services\Hse;

use App\Models\Company;
use App\Models\Department;
use App\Models\HseCorrectiveAction;
use App\Models\HseEnvironmentalRecord;
use App\Models\HseExposureHour;
use App\Models\HseFinding;
use App\Models\HseIncident;
use App\Models\HseInspection;
use App\Models\HseKpiTarget;
use App\Models\HsePermit;
use App\Models\HseRiskAssessment;
use App\Models\HseTrainingCourse;
use App\Models\HseTrainingRecord;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HseDashboardService
{
    private const LTIFR_MULTIPLIER = 1000000;

    private const TRIR_MULTIPLIER = 200000;

    private const TZ = 'Asia/Jakarta';

    /* =========================================================
     * FILTERS & OPTIONS
     * ========================================================= */

    public function filters(Request $request): array
    {
        $now = Carbon::now(self::TZ);
        $period = in_array($request->input('period'), ['monthly', 'quarterly', 'yearly'], true)
            ? $request->input('period') : 'monthly';
        $year = (int) $request->input('year', $now->year);
        $month = max(1, min(12, (int) $request->input('month', $now->month)));
        $quarter = max(1, min(4, (int) $request->input('quarter', (int) ceil($now->month / 3))));

        switch ($period) {
            case 'yearly':
                $from = Carbon::create($year, 1, 1, 0, 0, 0, self::TZ)->startOfDay();
                $to = Carbon::create($year, 12, 31, 23, 59, 59, self::TZ)->endOfDay();
                $label = (string) $year;
                break;
            case 'quarterly':
                $startMonth = (($quarter - 1) * 3) + 1;
                $from = Carbon::create($year, $startMonth, 1, 0, 0, 0, self::TZ)->startOfDay();
                $to = (clone $from)->addMonths(2)->endOfMonth()->endOfDay();
                $label = 'Q'.$quarter.' '.$year;
                break;
            default:
                $from = Carbon::create($year, $month, 1, 0, 0, 0, self::TZ)->startOfDay();
                $to = (clone $from)->endOfMonth()->endOfDay();
                $label = $from->translatedFormat('F Y');
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->input('date_from'), self::TZ)->startOfDay();
            $to = Carbon::parse($request->input('date_to'), self::TZ)->endOfDay();
            if ($to->lt($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
            $label = $from->format('d M Y').' – '.$to->format('d M Y');
        }

        $days = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $siteId = $request->filled('site_id') ? (int) $request->input('site_id') : \App\Support\SiteSelection::id($request);
        $companyId = $request->filled('company_id') ? (int) $request->input('company_id') : null;
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : ($request->filled('dept_id') ? (int) $request->input('dept_id') : null);

        $allowed = $request->user()?->allowedSiteIds();

        $scopeSiteIds = null;
        if ($siteId) {
            $scopeSiteIds = [$siteId];
        } elseif ($companyId) {
            $scopeSiteIds = Site::where('company_id', $companyId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        if ($allowed !== null) {
            $scopeSiteIds = $scopeSiteIds === null
                ? $allowed
                : array_values(array_intersect($scopeSiteIds, $allowed));
        }

        return [
            'period' => $period,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'from' => $from,
            'to' => $to,
            'prev_from' => $prevFrom,
            'prev_to' => $prevTo,
            'label' => $label,
            'site_id' => $siteId,
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'dept_id' => $departmentId,
            'scope_site_ids' => $scopeSiteIds,
            'allowed_site_ids' => $allowed,
        ];
    }

    public function options(array $filters): array
    {
        $allowed = $filters['allowed_site_ids'];

        $sitesQuery = Site::query()->with('company:id,code,name')->orderBy('code')->orderBy('name');
        if ($allowed !== null) {
            $sitesQuery->whereIn('id', $allowed ?: [-1]);
        }

        $departmentsQuery = Department::query()->orderBy('name');

        return [
            'sites' => $sitesQuery->get(['id', 'code', 'name', 'company_id', 'latitude', 'longitude']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'code', 'name']),
            'departments' => $departmentsQuery->get(['id', 'name']),
            'years' => range((int) now(self::TZ)->year, (int) now(self::TZ)->year - 4),
            'users' => \App\Models\User::where('is_active', true)->orderBy('name')->limit(200)->get(['id', 'name', 'email']),
            'training_courses' => \App\Models\HseTrainingCourse::orderBy('title')->get(['id', 'title']),
        ];
    }

    /* =========================================================
     * EXECUTIVE OVERVIEW
     * ========================================================= */

    public function overview(array $f): array
    {
        $incidents = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('category', 'Incident'));
        $incidentsPrev = $this->count(HseIncident::class, 'occurred_at', $f['prev_from'], $f['prev_to'], $f, fn ($q) => $q->where('category', 'Incident'));
        $nearMiss = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('category', 'Near Miss'));
        $nearMissPrev = $this->count(HseIncident::class, 'occurred_at', $f['prev_from'], $f['prev_to'], $f, fn ($q) => $q->where('category', 'Near Miss'));

        $inspectionScore = $this->avg(HseInspection::class, 'score', 'completed_date', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Completed'));
        $inspectionScorePrev = $this->avg(HseInspection::class, 'score', 'completed_date', $f['prev_from'], $f['prev_to'], $f, fn ($q) => $q->where('status', 'Completed'));

        $training = $this->trainingCompletion($f);
        $trainingPrev = $this->trainingCompletion(['from' => $f['prev_from'], 'to' => $f['prev_to']] + $f);

        $envCompliance = $this->environmentalCompliance($f);
        $openActions = $this->scoped(HseCorrectiveAction::class, $f)->whereNotIn('status', ['Closed'])->count();
        $openActionsPrev = $this->scoped(HseCorrectiveAction::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->where('created_at', '<=', $f['prev_to'])
            ->count();

        $lti = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'Lost Time Injury'));
        $ltiPrev = $this->count(HseIncident::class, 'occurred_at', $f['prev_from'], $f['prev_to'], $f, fn ($q) => $q->where('type', 'Lost Time Injury'));
        $daysWithoutLti = $this->daysWithoutLti($f);

        $targets = $this->targets();

        $kpis = [
            $this->kpi('total_incidents', 'Total Incidents', $incidents, $incidentsPrev, 'lower', $targets->get('total_incidents')?->target_value, 'record', route('hse.dashboard.incidents', $this->query($f)), 'Jumlah insiden (bukan near miss) pada periode terpilih.'),
            $this->kpi('total_near_misses', 'Total Near Misses', $nearMiss, $nearMissPrev, 'higher', $targets->get('total_near_misses')?->target_value, 'record', route('hse.dashboard.incidents', $this->query($f) + ['category' => 'Near Miss']), 'Jumlah laporan near miss pada periode terpilih.'),
            $this->kpi('inspection_score', 'Inspection Score', $inspectionScore, $inspectionScorePrev, 'higher', $targets->get('inspection_score')?->target_value ?? 90, '%', route('hse.dashboard.inspections', $this->query($f)), 'Rata-rata skor inspeksi yang berstatus Completed.'),
            $this->kpi('training_completion', 'Training Completion', $training, $trainingPrev, 'higher', $targets->get('training_completion')?->target_value ?? 100, '%', route('hse.dashboard.training', $this->query($f)), 'Record training berstatus Completed dibagi seluruh record training dalam cakupan.'),
            $this->kpi('environmental_compliance', 'Environmental Compliance', $envCompliance, null, 'higher', $targets->get('environmental_compliance')?->target_value ?? 100, '%', route('hse.dashboard.environment', $this->query($f)), 'Insiden lingkungan yang sudah Closed dibagi seluruh insiden lingkungan × 100.'),
            $this->kpi('open_actions', 'Open Corrective Actions', $openActions, $openActionsPrev, 'lower', $targets->get('open_actions')?->target_value, 'record', route('hse.dashboard.actions', $this->query($f) + ['status' => 'Open']), 'Corrective action yang belum berstatus Closed sampai akhir periode.'),
            $this->kpi('lti', 'Lost Time Injuries', $lti, $ltiPrev, 'lower', $targets->get('lti')?->target_value ?? 0, 'record', route('hse.dashboard.safety', $this->query($f)), 'Insiden dengan tipe Lost Time Injury.'),
            $this->kpi('days_without_lti', 'Days Without LTI', $daysWithoutLti, null, 'higher', null, 'hari', route('hse.dashboard.safety', $this->query($f)), 'Jarak hari sejak insiden Lost Time Injury terakhir sampai hari ini.'),
        ];

        $year = (int) $f['year'];
        $trend = $this->incidentTrend($f, $year);

        return [
            'kpis' => $kpis,
            'charts' => [
                'incident_trend' => $trend,
                'by_type' => $this->distribution(HseIncident::class, 'type', 'occurred_at', $f),
                'top_locations' => $this->topBySite(HseIncident::class, 'occurred_at', $f),
                'inspection_trend' => $this->monthlyAverage(HseInspection::class, 'score', 'completed_date', $year, $f, fn ($q) => $q->where('status', 'Completed')),
                'training_status' => $this->trainingStatusBreakdown($f),
                'action_status' => $this->distribution(HseCorrectiveAction::class, 'status', 'created_at', $f),
                'risk_matrix' => $this->riskMatrix($f),
                'environment' => $this->environmentTotals($f),
                'compliance' => $this->complianceSummary($f),
            ],
            'alerts' => $this->alerts($f),
            'recent' => $this->recentIncidents($f, 6),
            'upcoming_inspections' => $this->upcomingInspections($f, 5),
            'overdue_actions' => $this->overdueActions($f, 5),
        ];
    }

    private function incidentTrend(array $f, int $year): array
    {
        $current = $this->monthlyCounts(HseIncident::class, 'occurred_at', $year, $f, fn ($q) => $q->where('category', 'Incident'));
        $nearMiss = $this->monthlyCounts(HseIncident::class, 'occurred_at', $year, $f, fn ($q) => $q->where('category', 'Near Miss'));
        $prev = $this->monthlyCounts(HseIncident::class, 'occurred_at', $year - 1, $f, fn ($q) => $q->where('category', 'Incident'));

        return [
            'labels' => $this->monthLabels(),
            'series' => [
                ['name' => 'Incidents '.$year, 'data' => array_values($current), 'color' => '#1646A0'],
                ['name' => 'Near Miss '.$year, 'data' => array_values($nearMiss), 'color' => '#7C3AED'],
                ['name' => 'Incidents '.($year - 1), 'data' => array_values($prev), 'color' => '#94A3B8'],
            ],
        ];
    }

    /* =========================================================
     * SAFETY PERFORMANCE
     * ========================================================= */

    public function safety(array $f): array
    {
        $defs = [
            'Total Recordable Cases' => fn ($q) => $q->where('recordable', true),
            'Lost Time Injuries' => fn ($q) => $q->where('type', 'Lost Time Injury'),
            'Restricted Work Cases' => fn ($q) => $q->where('type', 'Restricted Work Case'),
            'Medical Treatment Cases' => fn ($q) => $q->where('type', 'Medical Treatment Case'),
            'First Aid Cases' => fn ($q) => $q->where('type', 'First Aid Case'),
            'Fatalities' => fn ($q) => $q->where('type', 'Fatality'),
        ];

        $current = [];
        $previous = [];
        foreach ($defs as $label => $constraint) {
            $current[$label] = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, $constraint);
            $previous[$label] = $this->count(HseIncident::class, 'occurred_at', $f['prev_from'], $f['prev_to'], $f, $constraint);
        }

        $exposure = $this->exposureHours($f);
        $lostDays = $this->sum(HseIncident::class, 'lost_time_days', 'occurred_at', $f['from'], $f['to'], $f);

        // Indikator tambahan sesuai spesifikasi SHE MANDALA
        $pdCount = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'Property Damage'));
        $pdfr = $exposure > 0 ? ($pdCount / $exposure) * self::LTIFR_MULTIPLIER : null;

        // PD Cost: sum from HseIncident property_damage_cost or scoped IndicatorDaily
        $incidentPdCost = (float) $this->sum(HseIncident::class, 'property_damage_cost', 'occurred_at', $f['from'], $f['to'], $f);
        $indicatorPdCost = 0.0;
        $pdCostInd = \App\Models\Indicator::where('code', 'PD_COST')->first();
        if ($pdCostInd) {
            $indQuery = \App\Models\IndicatorDaily::where('indicator_id', $pdCostInd->id)
                ->whereBetween('date', [$f['from']->toDateString(), $f['to']->toDateString()]);
            if (($f['scope_site_ids'] ?? null) !== null) {
                $indQuery->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
            }
            $indicatorPdCost = (float) $indQuery->sum('value');
        }
        $pdCost = $incidentPdCost > 0 ? $incidentPdCost : $indicatorPdCost;

        $enviroCount = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'Environmental'));
        $pakCount = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'PAK'));
        $kaptkCount = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'KAPTK'));

        // Near Miss: from Incident category/type Near Miss, with site-scoped HipoReport fallback
        $nearMissCount = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where(function ($sub) {
            $sub->where('category', 'Near Miss')->orWhere('type', 'Near Miss');
        }));
        if ($nearMissCount === 0) {
            $nearMissHipoQuery = \App\Models\HipoReport::whereBetween('report_time', [$f['from'], $f['to']])
                ->where(function ($q) {
                    $q->where('jenis_hipo', 'like', '%near%miss%')
                      ->orWhere('category', 'like', '%near%miss%');
                });
            if (($f['scope_site_ids'] ?? null) !== null) {
                $nearMissHipoQuery->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
            }
            $nearMissCount = $nearMissHipoQuery->count();
        }

        // HIPO: High, Extreme, Critical from HipoReport scoped to site
        $hipoQuery = \App\Models\HipoReport::whereBetween('report_time', [$f['from'], $f['to']])
            ->whereIn('risk_level', ['High', 'Extreme', 'Critical']);
        if (($f['scope_site_ids'] ?? null) !== null) {
            $hipoQuery->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
        }
        $hipoCount = $hipoQuery->count();

        $ltifr = $exposure > 0 ? (($current['Lost Time Injuries'] + $current['Fatalities']) / $exposure) * self::LTIFR_MULTIPLIER : null;
        $severityRate = $exposure > 0 ? ($lostDays / $exposure) * self::LTIFR_MULTIPLIER : null;

        // Dynamic targets from SheKpiTarget / HseKpiTarget
        $fatalityTarget = $this->resolveTargetValue('fatality', $f, 0);
        $ltiTarget = $this->resolveTargetValue('lti', $f, 0);
        $ltifrTarget = $this->resolveTargetValue('ltifr', $f, 0);
        $ltisrTarget = $this->resolveTargetValue('ltisr', $f, 0);
        $nonLtiTarget = $this->resolveTargetValue('injury_non_lti', $f, 0);
        $pdTarget = $this->resolveTargetValue('property_damage', $f, 0);
        $pdfrTarget = $this->resolveTargetValue('pdfr', $f, 0);
        $pdCostTarget = $this->resolveTargetValue('pd_cost', $f, 0);
        $enviroTarget = $this->resolveTargetValue('environmental', $f, 0);
        $pakTarget = $this->resolveTargetValue('pak', $f, 0);
        $kaptkTarget = $this->resolveTargetValue('kaptk', $f, 0);
        $nearMissTarget = $this->resolveTargetValue('near_miss', $f, null);
        $hipoTarget = $this->resolveTargetValue('hipo', $f, 0);
        $exposureTarget = $this->resolveTargetValue('manhours', $f, null);

        $kpis = [
            $this->kpi('fatality', 'Total Fatality', $current['Fatalities'], $previous['Fatalities'], 'lower', $fatalityTarget, 'kasus', route('hse.dashboard.performance.indicator', 'fatality'), 'Insiden kategori/tipe Fatality.'),
            $this->kpi('lti', 'Total Lost Time Injury (LTI)', $current['Lost Time Injuries'], $previous['Lost Time Injuries'], 'lower', $ltiTarget, 'kasus', route('hse.dashboard.performance.indicator', 'lti'), 'Insiden tipe Lost Time Injury.'),
            $this->kpi('ltifr', 'LTIFR', $ltifr, null, 'lower', $ltifrTarget, 'rate', null, '((LTI + Fatality) × 1.000.000) / Manhours. N/A bila manhours = 0.', 2),
            $this->kpi('ltisr', 'LTISR', $severityRate, null, 'lower', $ltisrTarget, 'rate', null, '(Total Lost Days × 1.000.000) / Manhours. N/A bila manhours = 0.', 2),
            $this->kpi('injury_non_lti', 'Injury Non-LTI', $current['Restricted Work Cases'] + $current['Medical Treatment Cases'] + $current['First Aid Cases'], null, 'lower', $nonLtiTarget, 'kasus', route('hse.dashboard.performance.indicator', 'non-lti'), 'First Aid + Medical Treatment + Restricted Work.'),
            $this->kpi('property_damage', 'Property Damage', $pdCount, null, 'lower', $pdTarget, 'kasus', route('hse.dashboard.performance.indicator', 'property-damage'), 'Insiden Property Damage.'),
            $this->kpi('pdfr', 'PDFR', $pdfr, null, 'lower', $pdfrTarget, 'rate', route('hse.dashboard.performance.indicator', 'pdfr'), '(Total Property Damage × 1.000.000) / Manhours.', 2),
            $this->kpi('pd_cost', 'Property Damage Cost', $pdCost, null, 'lower', $pdCostTarget, 'Rp', route('hse.dashboard.performance.indicator', 'pd-cost'), 'Total biaya kerusakan aset akibat insiden.'),
            $this->kpi('enviro_accident', 'Environmental Accident', $enviroCount, null, 'lower', $enviroTarget, 'kasus', route('hse.dashboard.performance.indicator', 'environmental'), 'Insiden pencemaran lingkungan.'),
            $this->kpi('pak', 'Penyakit Akibat Kerja (PAK)', $pakCount, null, 'lower', $pakTarget, 'kasus', route('hse.dashboard.performance.indicator', 'pak'), 'Diagnosa Penyakit Akibat Kerja tervalidasi.'),
            $this->kpi('kaptk', 'KAPTK', $kaptkCount, null, 'lower', $kaptkTarget, 'kasus', route('hse.dashboard.performance.indicator', 'kaptk'), 'Kejadian Akibat Penyakit Tenaga Kerja.'),
            $this->kpi('near_miss', 'Near Miss', $nearMissCount, null, 'higher', $nearMissTarget, 'laporan', route('hse.dashboard.performance.indicator', 'near-miss'), 'Total laporan hampir celaka (Nearmiss).'),
            $this->kpi('hipo', 'HIPO Near Miss', $hipoCount, null, 'lower', $hipoTarget, 'laporan', null, 'High Potential Incident / Near Miss.'),
            $this->kpi('exposure', 'Total Manhours', $exposure, null, 'higher', $exposureTarget, 'jam', route('hse.dashboard.manhours.index'), 'Total jam kerja tervalidasi.'),
            $this->kpi('days_without_lti', 'Days Without Accident', $this->daysWithoutLti($f) ?? 0, null, 'higher', null, 'hari', null, 'Hari kerja selamat tanpa kecelakaan.'),
            $this->kpi('fac', 'First Aid Cases', $current['First Aid Cases'], $previous['First Aid Cases'], 'lower', null, 'kasus', null, 'Pertolongan pertama.'),
            $this->kpi('mtc', 'Medical Treatment Cases', $current['Medical Treatment Cases'], $previous['Medical Treatment Cases'], 'lower', null, 'kasus', null, 'Perawatan medis lanjutan.'),
            $this->kpi('rwc', 'Restricted Work Cases', $current['Restricted Work Cases'], $previous['Restricted Work Cases'], 'lower', null, 'kasus', null, 'Kasus kerja terbatas.'),
        ];

        return [
            'kpis' => $kpis,
            'charts' => [
                'trend' => $this->safetyTrend($f, (int) $f['year']),
                'by_department' => $this->topByDepartment(HseIncident::class, 'occurred_at', $f),
                'by_site' => $this->topBySite(HseIncident::class, 'occurred_at', $f),
                'severity' => $this->distribution(HseIncident::class, 'severity', 'occurred_at', $f),
            ],
            'table' => $this->safetyDetail($f),
            'meta' => [
                'ltifr_multiplier' => self::LTIFR_MULTIPLIER,
                'trir_multiplier' => self::TRIR_MULTIPLIER,
                'exposure_hours' => $exposure,
            ],
        ];
    }

    private function safetyTrend(array $f, int $year): array
    {
        $lti = $this->monthlyCounts(HseIncident::class, 'occurred_at', $year, $f, fn ($q) => $q->where('type', 'Lost Time Injury'));
        $recordable = $this->monthlyCounts(HseIncident::class, 'occurred_at', $year, $f, fn ($q) => $q->where('recordable', true));

        return [
            'labels' => $this->monthLabels(),
            'series' => [
                ['name' => 'LTI', 'data' => array_values($lti), 'color' => '#DC2626'],
                ['name' => 'Recordable', 'data' => array_values($recordable), 'color' => '#1646A0'],
            ],
        ];
    }

    private function safetyDetail(array $f): array
    {
        return $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->with('site:id,code,name')
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get()
            ->map(fn (HseIncident $i) => [
                'id' => $i->id,
                'date' => $i->occurred_at?->format('d M Y'),
                'site' => $i->site?->name ?? '—',
                'type' => $i->type,
                'severity' => $i->severity,
                'recordable' => $i->recordable,
                'lost_days' => $i->lost_time_days,
                'status' => $i->status,
            ])->all();
    }

    /* =========================================================
     * INCIDENT & NEAR MISS ANALYTICS
     * ========================================================= */

    public function incidents(array $f): array
    {
        $total = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f);
        $nearMiss = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('category', 'Near Miss'));
        $highPotential = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->whereIn('severity', ['High', 'Critical']));
        $openInv = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('investigation_status', '!=', 'Completed'));
        $closedInv = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('investigation_status', 'Completed'));

        $avgDuration = $this->scoped(HseIncident::class, $f)
            ->where('investigation_status', 'Completed')
            ->whereNotNull('closed_at')
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->get()
            ->avg(fn (HseIncident $i) => $i->occurred_at && $i->closed_at ? $i->occurred_at->diffInDays($i->closed_at) : null);

        $highSeverity = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->whereIn('severity', ['High', 'Critical']));

        $kpis = [
            $this->kpi('total', 'Total Incidents', $total, null, 'lower', null, 'record', null, 'Seluruh laporan pada periode, termasuk near miss.'),
            $this->kpi('near_miss', 'Total Near Misses', $nearMiss, null, 'higher', null, 'record', null, 'category = Near Miss.'),
            $this->kpi('high_potential', 'High Potential Near Misses', $highPotential, null, 'lower', null, 'record', null, 'Near miss / insiden dengan severity High atau Critical.'),
            $this->kpi('open_inv', 'Open Investigations', $openInv, null, 'lower', null, 'record', null, 'investigation_status ≠ Completed.'),
            $this->kpi('closed_inv', 'Closed Investigations', $closedInv, null, 'higher', null, 'record', null, 'investigation_status = Completed.'),
            $this->kpi('avg_duration', 'Avg Investigation Duration', $avgDuration, null, 'lower', null, 'hari', null, 'Rata-rata selisih occurred_at ke closed_at untuk investigasi selesai.', 1),
            $this->kpi('high_severity', 'High Severity Cases', $highSeverity, null, 'lower', null, 'record', null, 'severity High atau Critical.'),
        ];

        $recent = $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->with(['site:id,code,name', 'department:id,name', 'investigator:id,name'])
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get();

        $timeOfDay = ['00-06' => 0, '06-12' => 0, '12-18' => 0, '18-24' => 0];
        foreach ($recent as $row) {
            $hour = (int) $row->occurred_at?->format('H');
            $bucket = match (true) {
                $hour < 6 => '00-06', $hour < 12 => '06-12', $hour < 18 => '12-18', default => '18-24'
            };
            $timeOfDay[$bucket]++;
        }

        return [
            'kpis' => $kpis,
            'charts' => [
                'trend' => $this->incidentTrend($f, (int) $f['year']),
                'by_type' => $this->distribution(HseIncident::class, 'type', 'occurred_at', $f),
                'by_severity' => $this->distribution(HseIncident::class, 'severity', 'occurred_at', $f),
                'by_department' => $this->topByDepartment(HseIncident::class, 'occurred_at', $f),
                'by_site' => $this->topBySite(HseIncident::class, 'occurred_at', $f),
                'by_activity' => $this->distribution(HseIncident::class, 'activity', 'occurred_at', $f, 8),
                'time_of_day' => ['labels' => array_keys($timeOfDay), 'data' => array_values($timeOfDay)],
                'investigation' => $this->distribution(HseIncident::class, 'investigation_status', 'occurred_at', $f),
            ],
            'rows' => $recent->map(fn (HseIncident $i) => [
                'id' => $i->id,
                'incident_no' => $i->incident_no ?: '#'.$i->id,
                'date' => $i->occurred_at?->format('d M Y'),
                'site' => $i->site?->name ?? '—',
                'department' => $i->department?->name ?? '—',
                'category' => $i->category,
                'type' => $i->type,
                'severity' => $i->severity,
                'investigation_status' => $i->investigation_status,
                'investigator' => $i->investigator?->name ?? '—',
                'status' => $i->status,
            ])->all(),
            'root_cause' => $this->rootCauseDistribution($f),
        ];
    }

    private function rootCauseDistribution(array $f): array
    {
        $rows = $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->whereNotNull('root_cause')
            ->pluck('root_cause');

        $groups = ['Human Factor' => 0, 'Equipment' => 0, 'Procedure' => 0, 'Environment' => 0, 'Other' => 0];
        foreach ($rows as $text) {
            $lower = strtolower((string) $text);
            $bucket = match (true) {
                str_contains($lower, 'manusia') || str_contains($lower, 'human') || str_contains($lower, 'behavior') => 'Human Factor',
                str_contains($lower, 'alat') || str_contains($lower, 'equipment') || str_contains($lower, 'mesin') => 'Equipment',
                str_contains($lower, 'prosedur') || str_contains($lower, 'procedure') || str_contains($lower, 'sop') => 'Procedure',
                str_contains($lower, 'lingkungan') || str_contains($lower, 'environment') || str_contains($lower, 'cuaca') => 'Environment',
                default => 'Other',
            };
            $groups[$bucket]++;
        }

        return ['labels' => array_keys($groups), 'data' => array_values($groups)];
    }

    /* =========================================================
     * INSPECTION & AUDIT
     * ========================================================= */

    public function inspections(array $f): array
    {
        $scheduled = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f);
        $completed = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Completed'));
        $pending = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f, fn ($q) => $q->whereIn('status', ['Scheduled', 'In Progress']));
        $overdue = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Overdue'));
        $avgScore = $this->avg(HseInspection::class, 'score', 'completed_date', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Completed'));

        $findingsTotal = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f);
        $findingsOpen = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->whereIn('status', ['Open', 'In Progress', 'Overdue']));
        $findingsClosed = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Closed'));
        $findingsOverdue = $this->scoped(HseFinding::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->whereDate('due_date', '<', now(self::TZ)->toDateString())
            ->count();

        $kpis = [
            $this->kpi('scheduled', 'Scheduled Inspections', $scheduled, null, 'higher', null, 'record', null, 'Inspeksi dengan scheduled_date dalam periode.'),
            $this->kpi('completed', 'Completed Inspections', $completed, null, 'higher', null, 'record', null, 'status = Completed.'),
            $this->kpi('pending', 'Pending Inspections', $pending, null, 'lower', null, 'record', null, 'status Scheduled atau In Progress.'),
            $this->kpi('overdue', 'Overdue Inspections', $overdue, null, 'lower', 0, 'record', null, 'status = Overdue.'),
            $this->kpi('avg_score', 'Average Inspection Score', $avgScore, null, 'higher', 90, '%', null, 'Rata-rata score inspeksi Completed.', 1),
            $this->kpi('findings_total', 'Total Audit Findings', $findingsTotal, null, 'lower', null, 'record', null, 'Seluruh finding inspeksi & audit yang dibuat pada periode.'),
            $this->kpi('findings_open', 'Open Findings', $findingsOpen, null, 'lower', null, 'record', null, 'status Open / In Progress / Overdue.'),
            $this->kpi('findings_closed', 'Closed Findings', $findingsClosed, null, 'higher', null, 'record', null, 'status = Closed.'),
            $this->kpi('findings_overdue', 'Overdue Findings', $findingsOverdue, null, 'lower', 0, 'record', null, 'due_date terlewat dan belum Closed.'),
        ];

        $upcoming = $this->scoped(HseInspection::class, $f)
            ->whereIn('status', ['Scheduled', 'In Progress'])
            ->whereDate('scheduled_date', '>=', now(self::TZ)->toDateString())
            ->orderBy('scheduled_date')
            ->limit(10)
            ->with('site:id,code,name')
            ->get();

        $history = $this->scoped(HseInspection::class, $f)
            ->whereBetween('scheduled_date', [$f['from'], $f['to']])
            ->orderByDesc('scheduled_date')
            ->limit(50)
            ->with('site:id,code,name')
            ->get();

        $findings = $this->scoped(HseFinding::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->orderByDesc('created_at')
            ->limit(50)
            ->with(['site:id,code,name', 'assignee:id,name'])
            ->get();

        return [
            'kpis' => $kpis,
            'charts' => [
                'completion' => $this->inspectionCompletionByMonth($f, (int) $f['year']),
                'score_trend' => $this->monthlyAverage(HseInspection::class, 'score', 'completed_date', (int) $f['year'], $f, fn ($q) => $q->where('status', 'Completed')),
                'findings_severity' => $this->distribution(HseFinding::class, 'severity', 'created_at', $f),
                'findings_department' => $this->topByDepartment(HseFinding::class, 'created_at', $f),
                'score_by_site' => $this->avgBySite(HseInspection::class, 'score', 'completed_date', $f, fn ($q) => $q->where('status', 'Completed')),
                'closure' => $this->distribution(HseFinding::class, 'status', 'created_at', $f),
                'aging' => $this->findingAging($f),
            ],
            'upcoming' => $upcoming->map(fn (HseInspection $i) => [
                'id' => $i->id,
                'title' => $i->title,
                'site' => $i->site?->name ?? '—',
                'date' => $i->scheduled_date?->format('d M Y'),
                'status' => $i->status,
            ])->all(),
            'history' => $history->map(fn (HseInspection $i) => [
                'id' => $i->id,
                'title' => $i->title,
                'site' => $i->site?->name ?? '—',
                'date' => $i->scheduled_date?->format('d M Y'),
                'score' => $i->score,
                'status' => $i->status,
            ])->all(),
            'findings' => $findings->map(fn (HseFinding $x) => [
                'id' => $x->id,
                'finding_no' => $x->finding_no ?: '#'.$x->id,
                'title' => $x->title,
                'site' => $x->site?->name ?? '—',
                'severity' => $x->severity,
                'status' => $x->status,
                'due_date' => $x->due_date?->format('d M Y'),
                'assignee' => $x->assignee?->name ?? '—',
            ])->all(),
        ];
    }

    private function inspectionCompletionByMonth(array $f, int $year): array
    {
        $scheduled = $this->monthlyCounts(HseInspection::class, 'scheduled_date', $year, $f);
        $completed = $this->monthlyCounts(HseInspection::class, 'completed_date', $year, $f, fn ($q) => $q->where('status', 'Completed'));

        return [
            'labels' => $this->monthLabels(),
            'series' => [
                ['name' => 'Scheduled', 'data' => array_values($scheduled), 'color' => '#94A3B8'],
                ['name' => 'Completed', 'data' => array_values($completed), 'color' => '#16A34A'],
            ],
        ];
    }

    private function findingAging(array $f): array
    {
        $rows = $this->scoped(HseFinding::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->whereNotNull('due_date')
            ->get();

        $buckets = ['Not due' => 0, '1-30 days' => 0, '31-60 days' => 0, '61-90 days' => 0, '>90 days' => 0];
        $today = now(self::TZ)->startOfDay();
        foreach ($rows as $row) {
            $days = $today->diffInDays($row->due_date, false);
            $bucket = match (true) {
                $days >= 0 => 'Not due',
                $days >= -30 => '1-30 days',
                $days >= -60 => '31-60 days',
                $days >= -90 => '61-90 days',
                default => '>90 days',
            };
            $buckets[$bucket]++;
        }

        return ['labels' => array_keys($buckets), 'data' => array_values($buckets)];
    }

    /* =========================================================
     * TRAINING & COMPETENCY
     * ========================================================= */

    public function training(array $f): array
    {
        $total = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])->count();
        $completed = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->where('status', 'Completed')->count();
        $expiring = $this->scoped(HseTrainingRecord::class, $f)
            ->where('status', 'Completed')
            ->whereBetween('expires_at', [now(self::TZ)->toDateString(), now(self::TZ)->addDays(90)->toDateString()])
            ->count();
        $expired = $this->scoped(HseTrainingRecord::class, $f)
            ->where('status', 'Completed')
            ->whereDate('expires_at', '<', now(self::TZ)->toDateString())
            ->count();
        $overdueMandatory = $this->scoped(HseTrainingRecord::class, $f)
            ->whereHas('course', fn ($q) => $q->where('is_mandatory', true))
            ->whereIn('status', ['Not Started', 'Scheduled', 'In Progress', 'Expired'])
            ->count();
        $scheduled = $this->scoped(HseTrainingRecord::class, $f)
            ->where('status', 'Scheduled')
            ->whereDate('scheduled_date', '>=', now(self::TZ)->toDateString())
            ->count();

        $completionRate = $total > 0 ? ($completed / $total) * 100 : null;
        $attendance = $this->scoped(HseTrainingRecord::class, $f)
            ->whereIn('status', ['Completed', 'In Progress'])
            ->whereBetween('created_at', [$f['from'], $f['to']])->count();
        $attendanceRate = $total > 0 ? ($attendance / $total) * 100 : null;

        $mandatoryTotal = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->whereHas('course', fn ($q) => $q->where('is_mandatory', true))
            ->count();
        $mandatoryCompleted = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->whereHas('course', fn ($q) => $q->where('is_mandatory', true))
            ->where('status', 'Completed')
            ->count();
        $mandatoryRate = $mandatoryTotal > 0 ? ($mandatoryCompleted / $mandatoryTotal) * 100 : null;

        $kpis = [
            $this->kpi('scope', 'Employees in Training Scope', $this->scoped(HseTrainingRecord::class, $f)->distinct('user_id')->count('user_id'), null, 'higher', null, 'orang', null, 'Jumlah user unik pada record training.'),
            $this->kpi('completion', 'Training Completion', $completionRate, null, 'higher', 100, '%', null, 'Completed / Total record training × 100.', 1),
            $this->kpi('mandatory', 'Mandatory Training Compliance', $mandatoryRate, null, 'higher', 100, '%', null, 'Record course mandatory yang Completed / seluruh record mandatory × 100.', 1),
            $this->kpi('upcoming', 'Upcoming Training Sessions', $scheduled, null, 'higher', null, 'sesi', null, 'status Scheduled dengan scheduled_date ≥ hari ini.'),
            $this->kpi('expired', 'Expired Competencies', $expired, null, 'lower', 0, 'record', null, 'status Completed dengan expires_at terlewat.'),
            $this->kpi('expiring', 'Expiring Certifications (90d)', $expiring, null, 'lower', 0, 'record', null, 'expires_at dalam 90 hari ke depan.'),
            $this->kpi('attendance', 'Training Attendance Rate', $attendanceRate, null, 'higher', 100, '%', null, 'Record Completed + In Progress / total record × 100.', 1),
            $this->kpi('overdue', 'Overdue Mandatory Training', $overdueMandatory, null, 'lower', 0, 'record', null, 'Record course mandatory yang belum Completed.'),
        ];

        $matrix = $this->trainingMatrix($f);

        return [
            'kpis' => $kpis,
            'charts' => [
                'completion_by_month' => $this->trainingCompletionByMonth($f, (int) $f['year']),
                'by_category' => $this->trainingByCategory($f),
                'by_department' => $this->trainingByDepartment($f),
                'attendance' => $this->trainingStatusBreakdown($f),
                'expiring' => $this->expiringCertifications($f),
            ],
            'matrix' => $matrix,
        ];
    }

    private function trainingCompletion(array $f): ?float
    {
        $total = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])->count();
        if ($total === 0) {
            return null;
        }
        $completed = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->where('status', 'Completed')->count();

        return ($completed / $total) * 100;
    }

    private function trainingStatusBreakdown(array $f): array
    {
        $rows = $this->scoped(HseTrainingRecord::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')->pluck('c', 'status');

        $labels = HseTrainingRecord::STATUSES;

        return [
            'labels' => $labels,
            'data' => array_map(fn ($s) => (int) ($rows[$s] ?? 0), $labels),
        ];
    }

    private function trainingCompletionByMonth(array $f, int $year): array
    {
        $completed = $this->monthlyCounts(HseTrainingRecord::class, 'completed_date', $year, $f, fn ($q) => $q->where('status', 'Completed'));
        $scheduled = $this->monthlyCounts(HseTrainingRecord::class, 'scheduled_date', $year, $f);

        return [
            'labels' => $this->monthLabels(),
            'series' => [
                ['name' => 'Scheduled', 'data' => array_values($scheduled), 'color' => '#94A3B8'],
                ['name' => 'Completed', 'data' => array_values($completed), 'color' => '#16A34A'],
            ],
        ];
    }

    private function trainingByCategory(array $f): array
    {
        $rows = $this->scoped(HseTrainingRecord::class, $f)
            ->join('hse_training_courses', 'hse_training_courses.id', '=', 'hse_training_records.course_id')
            ->whereBetween('hse_training_records.created_at', [$f['from'], $f['to']])
            ->select('hse_training_courses.category', DB::raw('COUNT(*) as c'))
            ->groupBy('hse_training_courses.category')
            ->pluck('c', 'category');

        return ['labels' => $rows->keys()->all(), 'data' => $rows->map(fn ($v) => (int) $v)->values()->all()];
    }

    private function trainingByDepartment(array $f): array
    {
        $rows = $this->scoped(HseTrainingRecord::class, $f)
            ->join('users', 'users.id', '=', 'hse_training_records.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->whereBetween('hse_training_records.created_at', [$f['from'], $f['to']])
            ->selectRaw("COALESCE(departments.name, 'Unassigned') as label, COUNT(*) as c")
            ->groupBy('label')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        return ['labels' => $rows->pluck('label')->all(), 'data' => $rows->pluck('c')->map(fn ($v) => (int) $v)->all()];
    }

    private function expiringCertifications(array $f): array
    {
        $rows = $this->scoped(HseTrainingRecord::class, $f)
            ->where('status', 'Completed')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', now(self::TZ)->addDays(120)->toDateString())
            ->orderBy('expires_at')
            ->limit(10)
            ->with('user:id,name', 'course:id,name')
            ->get();

        return $rows->map(fn (HseTrainingRecord $r) => [
            'name' => $r->course?->name ?? '—',
            'label' => $r->user?->name ?? '—',
            'date' => $r->expires_at?->format('d M Y'),
        ])->all();
    }

    private function trainingMatrix(array $f): array
    {
        $courses = HseTrainingCourse::where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']);
        $records = $this->scoped(HseTrainingRecord::class, $f)
            ->with('user:id,name,department_id')
            ->get()
            ->groupBy('user_id');

        $rows = $records->map(function (Collection $items) use ($courses) {
            $user = $items->first()->user;
            $cells = [];
            foreach ($courses as $course) {
                $record = $items->firstWhere('course_id', $course->id);
                $cells[$course->id] = $record
                    ? $this->trainingCellStatus($record)
                    : 'Not applicable';
            }

            return [
                'user' => $user?->name ?? '—',
                'cells' => $cells,
            ];
        })->values()->all();

        return ['courses' => $courses->all(), 'rows' => $rows];
    }

    private function trainingCellStatus(HseTrainingRecord $record): string
    {
        if ($record->status === 'Completed' && $record->expires_at && $record->expires_at->isPast()) {
            return 'Expired';
        }

        return $record->status;
    }

    /* =========================================================
     * ENVIRONMENTAL
     * ========================================================= */

    public function environment(array $f): array
    {
        $totals = $this->environmentTotals($f);
        $get = fn (string $metric) => (float) ($totals[$metric]['value'] ?? 0);
        $unit = fn (string $metric) => $totals[$metric]['unit'] ?? '';

        $waste = $get('Waste Generated');
        $hazardous = $get('Hazardous Waste');
        $nonHazardous = $get('Non-Hazardous Waste');
        $recycled = $get('Recycled Waste');
        $recyclingRate = $waste > 0 ? ($recycled / $waste) * 100 : null;
        $envIncidents = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'Environmental'));
        $compliance = $this->environmentalCompliance($f);

        $kpis = [
            $this->kpi('waste', 'Total Waste Generated', $waste, null, 'lower', null, $unit('Waste Generated') ?: 'kg', null, 'SUM value metric Waste Generated.', 2),
            $this->kpi('hazardous', 'Hazardous Waste', $hazardous, null, 'lower', null, $unit('Hazardous Waste') ?: 'kg', null, 'SUM value metric Hazardous Waste.', 2),
            $this->kpi('non_hazardous', 'Non-Hazardous Waste', $nonHazardous, null, 'lower', null, $unit('Non-Hazardous Waste') ?: 'kg', null, 'SUM value metric Non-Hazardous Waste.', 2),
            $this->kpi('recycling', 'Recycling Rate', $recyclingRate, null, 'higher', 100, '%', null, 'Recycled Waste / Waste Generated × 100.', 1),
            $this->kpi('energy', 'Energy Consumption', $get('Energy Consumption'), null, 'lower', null, $unit('Energy Consumption') ?: 'kWh', null, 'SUM value metric Energy Consumption.', 2),
            $this->kpi('water', 'Water Consumption', $get('Water Consumption'), null, 'lower', null, $unit('Water Consumption') ?: 'm³', null, 'SUM value metric Water Consumption.', 2),
            $this->kpi('fuel', 'Fuel Consumption', $get('Fuel Consumption'), null, 'lower', null, $unit('Fuel Consumption') ?: 'L', null, 'SUM value metric Fuel Consumption.', 2),
            $this->kpi('incidents', 'Environmental Incidents', $envIncidents, null, 'lower', 0, 'record', null, 'Insiden tipe Environmental.'),
            $this->kpi('compliance', 'Environmental Compliance', $compliance, null, 'higher', 100, '%', null, 'Insiden lingkungan Closed / total insiden lingkungan × 100.', 1),
        ];

        return [
            'kpis' => $kpis,
            'charts' => [
                'waste_trend' => $this->environmentTrend($f, (int) $f['year'], 'Waste Generated'),
                'waste_type' => $this->wasteTypeBreakdown($f),
                'recycling' => $this->recyclingByMonth($f, (int) $f['year']),
                'energy' => $this->environmentTrend($f, (int) $f['year'], 'Energy Consumption'),
                'water' => $this->environmentTrend($f, (int) $f['year'], 'Water Consumption'),
                'fuel' => $this->environmentTrend($f, (int) $f['year'], 'Fuel Consumption'),
                'incidents_by_site' => $this->topBySite(HseIncident::class, 'occurred_at', $f, fn ($q) => $q->where('type', 'Environmental')),
                'targets' => $this->environmentTargets($f),
            ],
            'totals' => $totals,
        ];
    }

    private function environmentTotals(array $f): array
    {
        $rows = $this->scoped(HseEnvironmentalRecord::class, $f)
            ->whereBetween('recorded_date', [$f['from'], $f['to']])
            ->select('metric', 'unit', DB::raw('SUM(value) as total'))
            ->groupBy('metric', 'unit')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->metric] = ['value' => (float) $row->total, 'unit' => $row->unit];
        }

        return $out;
    }

    private function environmentalCompliance(array $f): ?float
    {
        $q = $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->where('type', 'Environmental');
        $total = (clone $q)->count();
        if ($total === 0) {
            return 100.0;
        }
        $closed = (clone $q)->where('status', 'Closed')->count();

        return ($closed / $total) * 100;
    }

    private function environmentTrend(array $f, int $year, string $metric): array
    {
        $rows = $this->scoped(HseEnvironmentalRecord::class, $f)
            ->where('metric', $metric)
            ->whereYear('recorded_date', $year)
            ->get(['recorded_date', 'value']);

        $buckets = array_fill_keys($this->monthLabels(), 0.0);
        foreach ($rows as $row) {
            $key = $row->recorded_date?->translatedFormat('M');
            if ($key && array_key_exists($key, $buckets)) {
                $buckets[$key] += (float) $row->value;
            }
        }

        return [
            'labels' => array_keys($buckets),
            'series' => [['name' => $metric, 'data' => array_values($buckets), 'color' => '#1646A0']],
        ];
    }

    private function wasteTypeBreakdown(array $f): array
    {
        $totals = $this->environmentTotals($f);
        $labels = ['Hazardous Waste', 'Non-Hazardous Waste', 'Recycled Waste'];
        $data = array_map(fn ($l) => (float) ($totals[$l]['value'] ?? 0), $labels);

        return ['labels' => $labels, 'data' => $data];
    }

    private function recyclingByMonth(array $f, int $year): array
    {
        $waste = $this->environmentTrend($f, $year, 'Waste Generated')['series'][0]['data'];
        $recycled = $this->environmentTrend($f, $year, 'Recycled Waste')['series'][0]['data'];
        $rate = [];
        foreach ($waste as $i => $total) {
            $rate[] = $total > 0 ? round(($recycled[$i] / $total) * 100, 1) : 0;
        }

        return [
            'labels' => $this->monthLabels(),
            'series' => [['name' => 'Recycling Rate', 'data' => $rate, 'color' => '#16A34A']],
        ];
    }

    private function environmentTargets(array $f): array
    {
        $targets = HseKpiTarget::where('key', 'like', 'env.%')->get()->keyBy('key');
        $totals = $this->environmentTotals($f);

        $map = [
            'env.waste' => 'Waste Generated',
            'env.energy' => 'Energy Consumption',
            'env.water' => 'Water Consumption',
            'env.fuel' => 'Fuel Consumption',
        ];

        $labels = [];
        $actual = [];
        $target = [];
        foreach ($map as $key => $metric) {
            $labels[] = $metric;
            $actual[] = (float) ($totals[$metric]['value'] ?? 0);
            $target[] = (float) ($targets->get($key)?->target_value ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Actual', 'data' => $actual, 'color' => '#1646A0'],
                ['name' => 'Target', 'data' => $target, 'color' => '#F59E0B'],
            ],
        ];
    }

    /* =========================================================
     * RISK & COMPLIANCE
     * ========================================================= */

    public function riskCompliance(array $f): array
    {
        $active = $this->count(HseRiskAssessment::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Active'));
        $high = $this->scoped(HseRiskAssessment::class, $f)->where('status', 'Active')->where('risk_level', 'High')->count();
        $extreme = $this->scoped(HseRiskAssessment::class, $f)->where('status', 'Active')->where('risk_level', 'Extreme')->count();
        $needsTreatment = $this->scoped(HseRiskAssessment::class, $f)->where('status', 'Active')->whereIn('risk_level', ['High', 'Extreme'])->count();
        $treatmentDone = $this->scoped(HseRiskAssessment::class, $f)
            ->whereIn('risk_level', ['High', 'Extreme'])
            ->where('treatment_status', 'Completed')
            ->count();
        $treatmentRate = $needsTreatment > 0 ? ($treatmentDone / $needsTreatment) * 100 : null;
        $compliance = $this->riskComplianceRate($f);
        $expiredPermits = $this->scoped(HsePermit::class, $f)->where(function ($q) {
            $q->where('status', 'Expired')->orWhereDate('valid_to', '<', now(self::TZ)->toDateString());
        })->count();
        $overdueRequirements = $this->scoped(HseFinding::class, $f)->whereNotIn('status', ['Closed'])->whereDate('due_date', '<', now(self::TZ)->toDateString())->count();

        $kpis = [
            $this->kpi('active', 'Active Risk Assessments', $active, null, 'lower', null, 'record', null, 'Risk assessment status Active yang dibuat dalam periode.'),
            $this->kpi('high', 'High Risk Count', $high, null, 'lower', null, 'record', null, 'risk_level = High dan status Active.'),
            $this->kpi('extreme', 'Extreme Risk Count', $extreme, null, 'lower', 0, 'record', null, 'risk_level = Extreme dan status Active.'),
            $this->kpi('treatment', 'Risks Requiring Treatment', $needsTreatment, null, 'lower', null, 'record', null, 'Risiko High/Extreme yang masih Active.'),
            $this->kpi('treatment_rate', 'Risk Treatment Completion', $treatmentRate, null, 'higher', 100, '%', null, 'treatment_status Completed / risiko yang memerlukan treatment × 100.', 1),
            $this->kpi('compliance', 'Compliance Percentage', $compliance, null, 'higher', 100, '%', null, 'Finding Closed / total finding × 100.', 1),
            $this->kpi('expired_permits', 'Expired Permits', $expiredPermits, null, 'lower', 0, 'record', null, 'Permit Expired atau valid_to terlewat.'),
            $this->kpi('overdue_req', 'Overdue Compliance Requirements', $overdueRequirements, null, 'lower', 0, 'record', null, 'Finding belum Closed dengan due_date terlewat.'),
        ];

        $highRisk = $this->scoped(HseRiskAssessment::class, $f)
            ->where('risk_level', 'High')->where('status', 'Active')
            ->orderByDesc('residual_score')->limit(10)->with('site:id,code,name')->get();

        $extremeRisk = $this->scoped(HseRiskAssessment::class, $f)
            ->where('risk_level', 'Extreme')->where('status', 'Active')
            ->orderByDesc('residual_score')->limit(10)->with('site:id,code,name')->get();

        $overdueTreatment = $this->scoped(HseRiskAssessment::class, $f)
            ->whereNotIn('treatment_status', ['Completed', 'Not Required'])
            ->whereDate('treatment_due_date', '<', now(self::TZ)->toDateString())
            ->orderBy('treatment_due_date')->limit(10)->with('site:id,code,name')->get();

        $expiringPermits = $this->scoped(HsePermit::class, $f)
            ->whereDate('valid_to', '>=', now(self::TZ)->toDateString())
            ->whereDate('valid_to', '<=', now(self::TZ)->addDays(60)->toDateString())
            ->orderBy('valid_to')->limit(10)->with('site:id,code,name')->get();

        $nonCompliance = $this->scoped(HseFinding::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->orderBy('due_date')->limit(10)->with('site:id,code,name')->get();

        return [
            'kpis' => $kpis,
            'charts' => [
                'matrix' => $this->riskMatrix($f),
                'inherent_residual' => $this->inherentVsResidual($f),
                'by_category' => $this->distribution(HseRiskAssessment::class, 'category', 'created_at', $f),
                'by_site' => $this->topBySite(HseRiskAssessment::class, 'created_at', $f),
                'high_activity' => $this->distribution(HseRiskAssessment::class, 'activity', 'created_at', $f, 10),
                'treatment_progress' => $this->distribution(HseRiskAssessment::class, 'treatment_status', 'created_at', $f),
                'permit_timeline' => $this->distribution(HsePermit::class, 'status', 'valid_to', $f),
                'requirement_status' => $this->distribution(HseFinding::class, 'status', 'created_at', $f),
            ],
            'high_risk' => $this->riskRows($highRisk),
            'extreme_risk' => $this->riskRows($extremeRisk),
            'overdue_treatment' => $this->riskRows($overdueTreatment),
            'expiring_permits' => $expiringPermits->map(fn (HsePermit $p) => [
                'id' => $p->id,
                'permit_no' => $p->permit_no ?: '#'.$p->id,
                'type' => $p->type,
                'site' => $p->site?->name ?? '—',
                'valid_to' => $p->valid_to?->format('d M Y'),
                'status' => $p->status,
            ])->all(),
            'non_compliance' => $nonCompliance->map(fn (HseFinding $x) => [
                'id' => $x->id,
                'title' => $x->title,
                'site' => $x->site?->name ?? '—',
                'severity' => $x->severity,
                'status' => $x->status,
                'due_date' => $x->due_date?->format('d M Y'),
            ])->all(),
        ];
    }

    private function riskComplianceRate(array $f): ?float
    {
        $total = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f);
        if ($total === 0) {
            return 100.0;
        }
        $closed = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Closed'));

        return ($closed / $total) * 100;
    }

    private function riskRows(Collection $rows): array
    {
        return $rows->map(fn (HseRiskAssessment $r) => [
            'id' => $r->id,
            'activity' => $r->activity,
            'hazard' => $r->hazard,
            'site' => $r->site?->name ?? '—',
            'likelihood' => $r->residual_likelihood,
            'consequence' => $r->residual_consequence,
            'score' => $r->residual_score,
            'level' => $r->risk_level,
            'treatment_status' => $r->treatment_status,
            'due_date' => $r->treatment_due_date?->format('d M Y'),
        ])->all();
    }

    private function inherentVsResidual(array $f): array
    {
        $rows = $this->scoped(HseRiskAssessment::class, $f)->where('status', 'Active')->get();
        $buckets = array_fill_keys(HseRiskAssessment::LEVELS, 0);
        $residual = $buckets;
        foreach ($rows as $row) {
            $buckets[HseRiskAssessment::levelForScore($row->inherent_score)]++;
            $residual[$row->risk_level] = ($residual[$row->risk_level] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($buckets),
            'series' => [
                ['name' => 'Inherent', 'data' => array_values($buckets), 'color' => '#DC2626'],
                ['name' => 'Residual', 'data' => array_values($residual), 'color' => '#16A34A'],
            ],
        ];
    }

    public function riskMatrix(array $f): array
    {
        $rows = $this->scoped(HseRiskAssessment::class, $f)
            ->where('status', 'Active')
            ->get(['residual_likelihood', 'residual_consequence']);

        $matrix = [];
        for ($likelihood = 5; $likelihood >= 1; $likelihood--) {
            for ($consequence = 1; $consequence <= 5; $consequence++) {
                $matrix[$likelihood][$consequence] = 0;
            }
        }
        foreach ($rows as $row) {
            $l = max(1, min(5, (int) $row->residual_likelihood));
            $c = max(1, min(5, (int) $row->residual_consequence));
            $matrix[$l][$c]++;
        }

        return $matrix;
    }

    /* =========================================================
     * CORRECTIVE ACTIONS
     * ========================================================= */

    public function actions(array $f): array
    {
        $total = $this->count(HseCorrectiveAction::class, 'created_at', $f['from'], $f['to'], $f);
        $open = $this->count(HseCorrectiveAction::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Open'));
        $progress = $this->count(HseCorrectiveAction::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'In Progress'));
        $pending = $this->count(HseCorrectiveAction::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Pending Verification'));
        $closed = $this->count(HseCorrectiveAction::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Closed'));
        $overdue = $this->scoped(HseCorrectiveAction::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->whereDate('due_date', '<', now(self::TZ)->toDateString())->count();
        $onTime = $this->scoped(HseCorrectiveAction::class, $f)
            ->where('status', 'Closed')
            ->whereColumn('closed_at', '<=', 'due_date')->count();
        $onTimeRate = $closed > 0 ? ($onTime / $closed) * 100 : null;
        $avgClosure = $this->scoped(HseCorrectiveAction::class, $f)
            ->where('status', 'Closed')->whereNotNull('closed_at')
            ->get()->avg(fn (HseCorrectiveAction $a) => $a->created_at && $a->closed_at ? $a->created_at->diffInDays($a->closed_at) : null);

        $kpis = [
            $this->kpi('total', 'Total Actions', $total, null, 'lower', null, 'record', null, 'Seluruh corrective action yang dibuat pada periode.'),
            $this->kpi('open', 'Open Actions', $open, null, 'lower', null, 'record', null, 'status = Open.'),
            $this->kpi('progress', 'In Progress', $progress, null, 'lower', null, 'record', null, 'status = In Progress.'),
            $this->kpi('pending', 'Pending Verification', $pending, null, 'lower', null, 'record', null, 'status = Pending Verification.'),
            $this->kpi('closed', 'Closed Actions', $closed, null, 'higher', null, 'record', null, 'status = Closed.'),
            $this->kpi('overdue', 'Overdue Actions', $overdue, null, 'lower', 0, 'record', null, 'Belum Closed dan due_date terlewat.'),
            $this->kpi('on_time', 'On-Time Completion Rate', $onTimeRate, null, 'higher', 100, '%', null, 'Closed dengan closed_at ≤ due_date / total Closed × 100.', 1),
            $this->kpi('avg_closure', 'Average Closure Duration', $avgClosure, null, 'lower', null, 'hari', null, 'Rata-rata selisih created_at ke closed_at untuk action Closed.', 1),
        ];

        $rows = $this->scoped(HseCorrectiveAction::class, $f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->with(['site:id,code,name', 'assignee:id,name'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(100)
            ->get();

        return [
            'kpis' => $kpis,
            'charts' => [
                'status' => $this->distribution(HseCorrectiveAction::class, 'status', 'created_at', $f),
                'trend' => $this->actionTrend($f, (int) $f['year']),
                'by_source' => $this->distribution(HseCorrectiveAction::class, 'source_module', 'created_at', $f),
                'by_priority' => $this->distribution(HseCorrectiveAction::class, 'priority', 'created_at', $f),
                'by_department' => $this->topByDepartment(HseCorrectiveAction::class, 'created_at', $f),
                'aging' => $this->actionAging($f),
                'sla' => $this->slaCompliance($f),
                'monthly_closure' => $this->monthlyCounts(HseCorrectiveAction::class, 'closed_at', (int) $f['year'], $f, fn ($q) => $q->where('status', 'Closed')),
            ],
            'rows' => $rows->map(fn (HseCorrectiveAction $a) => [
                'id' => $a->id,
                'action_no' => $a->action_no ?: '#'.$a->id,
                'source' => $a->source_module ?: '—',
                'title' => $a->title,
                'priority' => $a->priority,
                'pic' => $a->assignee?->name ?? '—',
                'due_date' => $a->due_date?->format('d M Y'),
                'status' => $a->status,
                'days_overdue' => $a->due_date && ! $a->due_date->isFuture() && $a->status !== 'Closed'
                    ? $a->due_date->diffInDays(now(self::TZ)->startOfDay()) : 0,
                'verification' => $a->verified_at ? 'Verified' : 'Pending',
            ])->all(),
        ];
    }

    private function actionTrend(array $f, int $year): array
    {
        $opened = $this->monthlyCounts(HseCorrectiveAction::class, 'created_at', $year, $f);
        $closed = $this->monthlyCounts(HseCorrectiveAction::class, 'closed_at', $year, $f, fn ($q) => $q->where('status', 'Closed'));

        return [
            'labels' => $this->monthLabels(),
            'series' => [
                ['name' => 'Opened', 'data' => array_values($opened), 'color' => '#1646A0'],
                ['name' => 'Closed', 'data' => array_values($closed), 'color' => '#16A34A'],
            ],
        ];
    }

    private function actionAging(array $f): array
    {
        $rows = $this->scoped(HseCorrectiveAction::class, $f)
            ->whereNotIn('status', ['Closed'])->whereNotNull('due_date')->get(['due_date']);
        $buckets = ['Not due' => 0, '1-30 days' => 0, '31-60 days' => 0, '61-90 days' => 0, '>90 days' => 0];
        $today = now(self::TZ)->startOfDay();
        foreach ($rows as $row) {
            $days = $today->diffInDays($row->due_date, false);
            $bucket = match (true) {
                $days >= 0 => 'Not due',
                $days >= -30 => '1-30 days',
                $days >= -60 => '31-60 days',
                $days >= -90 => '61-90 days',
                default => '>90 days',
            };
            $buckets[$bucket]++;
        }

        return ['labels' => array_keys($buckets), 'data' => array_values($buckets)];
    }

    private function slaCompliance(array $f): array
    {
        $rows = $this->scoped(HseCorrectiveAction::class, $f)
            ->where('status', 'Closed')->get(['closed_at', 'due_date']);
        $onTime = 0;
        $late = 0;
        foreach ($rows as $row) {
            if (! $row->due_date) {
                continue;
            }
            if ($row->closed_at && $row->closed_at->toDateString() <= $row->due_date->toDateString()) {
                $onTime++;
            } else {
                $late++;
            }
        }

        return ['labels' => ['On Time', 'Late'], 'data' => [$onTime, $late]];
    }

    /* =========================================================
     * SITE & LOCATION
     * ========================================================= */

    public function sites(array $f): array
    {
        $sites = Site::query()->with('company:id,code,name')->orderBy('name');
        if ($f['scope_site_ids'] !== null) {
            $sites->whereIn('id', $f['scope_site_ids'] ?: [-1]);
        }
        $sites = $sites->get();

        $cards = $sites->map(function (Site $site) use ($f) {
            $scope = ['scope_site_ids' => [$site->id]] + $f;

            $incidents = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $scope, fn ($q) => $q->where('category', 'Incident'));
            $nearMiss = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $scope, fn ($q) => $q->where('category', 'Near Miss'));
            $inspectionScore = $this->avg(HseInspection::class, 'score', 'completed_date', $f['from'], $f['to'], $scope, fn ($q) => $q->where('status', 'Completed'));
            $openActions = $this->scoped(HseCorrectiveAction::class, $scope)->whereNotIn('status', ['Closed'])->count();
            $training = $this->trainingCompletion(['from' => $f['from'], 'to' => $f['to']] + $scope);
            $highRisk = $this->scoped(HseRiskAssessment::class, $scope)->where('status', 'Active')->whereIn('risk_level', ['High', 'Extreme'])->count();

            return [
                'id' => $site->id,
                'code' => $site->code,
                'name' => $site->name,
                'company' => $site->company?->name,
                'incidents' => $incidents,
                'near_miss' => $nearMiss,
                'inspection_score' => $inspectionScore,
                'open_actions' => $openActions,
                'training_completion' => $training,
                'high_risk' => $highRisk,
                'latitude' => $site->latitude,
                'longitude' => $site->longitude,
            ];
        })->values();

        $withCoords = $cards->filter(fn ($c) => $c['latitude'] !== null && $c['longitude'] !== null)->values();

        return [
            'cards' => $cards->all(),
            'map' => [
                'points' => $withCoords->map(fn ($c) => [
                    'name' => $c['name'],
                    'code' => $c['code'],
                    'latitude' => $c['latitude'],
                    'longitude' => $c['longitude'],
                    'incidents' => $c['incidents'],
                    'inspection_score' => $c['inspection_score'],
                    'open_actions' => $c['open_actions'],
                ])->all(),
                'missing' => $cards->count() - $withCoords->count(),
            ],
            'charts' => [
                'incidents' => $this->siteSeries($cards, 'incidents'),
                'near_miss' => $this->siteSeries($cards, 'near_miss'),
                'inspection' => $this->siteSeries($cards, 'inspection_score'),
                'actions' => $this->siteSeries($cards, 'open_actions'),
                'training' => $this->siteSeries($cards, 'training_completion'),
            ],
        ];
    }

    private function siteSeries(Collection $cards, string $key): array
    {
        return [
            'labels' => $cards->pluck('name')->all(),
            'data' => $cards->pluck($key)->map(fn ($v) => $v === null ? 0 : round((float) $v, 1))->all(),
        ];
    }

    /* =========================================================
     * REPORTS
     * ========================================================= */

    public function reports(array $f): array
    {
        $catalog = [
            ['key' => 'executive', 'title' => 'Executive SHE Report', 'description' => 'Ringkasan komprehensif indikator SHE eksekutif, status kepatuhan, dan pencapaian target.', 'route' => 'hse.dashboard.overview'],
            ['key' => 'safety', 'title' => 'Monthly Performance', 'description' => 'Kinerja bulanan LTI, TRIR, LTIFR, Jam Kerja Aman (Manhours), dan Severity Rate.', 'route' => 'hse.dashboard.safety'],
            ['key' => 'incidents', 'title' => 'Incident Report', 'description' => 'Laporan insiden, kecelakaan kerja, near miss, kategori, dan investigasi.', 'route' => 'hse.dashboard.incidents'],
            ['key' => 'sap', 'title' => 'SAP Achievement Report', 'description' => 'Pencapaian target Safety Accountability Program (Inspeksi, PTO, Hazard Report).', 'route' => 'hse.dashboard.sap.index'],
            ['key' => 'risk', 'title' => 'Risk Register Report', 'description' => 'Daftar asesmen risiko operasional, matriks 5x5, dan pemantauan kontrol kritis.', 'route' => 'hse.dashboard.risk.dashboard'],
            ['key' => 'actions', 'title' => 'PICA Report', 'description' => 'Tindakan korektif & preventif (PICA), status verifikasi, SLA closure, dan overdue.', 'route' => 'hse.dashboard.pica.dashboard'],
            ['key' => 'sites', 'title' => 'Site Comparison Report', 'description' => 'Benchmarking dan perbandingan kinerja SHE lintas jobsite.', 'route' => 'hse.dashboard.sites'],
            ['key' => 'manhours', 'title' => 'Manhours Report', 'description' => 'Rekapitulasi jam kerja orang (manhours), headcount karyawan, dan safe manhours.', 'route' => 'hse.dashboard.manhours.index'],
            ['key' => 'property_damage', 'title' => 'Property Damage Report', 'description' => 'Kerusakan aset/properti, estimasi kerugian biaya, dan kategori insiden.', 'route' => 'hse.dashboard.incidents'],
        ];

        $history = \App\Models\HseReportRun::query()
            ->with('generator:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($run) => [
                'id' => $run->id,
                'title' => $run->title,
                'period' => ($run->period_from?->format('d M Y') ?? '—').' – '.($run->period_to?->format('d M Y') ?? '—'),
                'format' => strtoupper($run->format),
                'status' => $run->status,
                'generated_by' => $run->generator?->name ?? '—',
                'created_at' => $run->created_at?->format('d M Y H:i'),
            ])->all();

        return ['catalog' => $catalog, 'history' => $history];
    }

    public function reportData(string $key, array $f): array
    {
        return match ($key) {
            'safety' => $this->safety($f),
            'incidents' => $this->incidents($f),
            'sap' => $this->sapSummaryData($f),
            'inspections', 'audit' => $this->inspections($f),
            'training' => $this->training($f),
            'environment' => $this->environment($f),
            'risk' => $this->riskCompliance($f),
            'actions' => $this->actions($f),
            'sites' => $this->sites($f),
            'manhours' => $this->manhoursSummaryData($f),
            'property_damage' => $this->propertyDamageData($f),
            default => $this->overview($f),
        };
    }

    private function sapSummaryData(array $f): array
    {
        $insp = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f);
        $pto = $this->count(HseInspection::class, 'scheduled_date', $f['from'], $f['to'], $f, fn ($q) => $q->where('type', 'PTO'));
        $hazard = $this->count(HipoReport::class, 'report_time', $f['from'], $f['to'], $f);

        return [
            'kpis' => [
                $this->kpi('sap_total', 'Total SAP Activities', $insp + $hazard, null, 'higher', null, 'aktivitas'),
                $this->kpi('sap_insp', 'Inspeksi Selesai', $insp, null, 'higher', null, 'inspeksi'),
                $this->kpi('sap_pto', 'PTO Selesai', $pto, null, 'higher', null, 'observasi'),
                $this->kpi('sap_hazard', 'Hazard Report Disubmit', $hazard, null, 'higher', null, 'laporan'),
            ],
            'table' => [],
        ];
    }

    private function manhoursSummaryData(array $f): array
    {
        $query = $this->scoped(SheManhour::class, $f)
            ->whereBetween('date', [$f['from']->toDateString(), $f['to']->toDateString()])
            ->where('status', 'Validated');

        $manhours = (float) (clone $query)->sum('manhours');
        $headcount = (float) (clone $query)->avg('employee_count');

        return [
            'kpis' => [
                $this->kpi('total_manhours', 'Total Validated Manhours', $manhours, null, 'higher', null, 'jam', null, 'Total jam kerja tervalidasi.'),
                $this->kpi('avg_headcount', 'Average Headcount', round($headcount ?? 0), null, 'higher', null, 'karyawan', null, 'Rata-rata tenaga kerja.'),
            ],
            'table' => [],
        ];
    }

    private function propertyDamageData(array $f): array
    {
        $rows = $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->where('type', 'Property Damage')
            ->get();

        return [
            'kpis' => [
                $this->kpi('pd_count', 'Total Property Damage Incidents', $rows->count(), null, 'lower', 0, 'kasus'),
            ],
            'table' => $rows->map(fn ($r) => [
                'incident_no' => $r->incident_no,
                'occurred_at' => $r->occurred_at?->format('d M Y H:i'),
                'site' => $r->site?->name ?? '—',
                'severity' => $r->severity,
                'description' => $r->description,
                'status' => $r->status,
            ])->all(),
        ];
    }

    /* =========================================================
     * SHARED HELPERS
     * ========================================================= */

    private function scoped(string $model, array $f): Builder
    {
        $query = $model::query();
        if (($f['scope_site_ids'] ?? null) !== null) {
            $query->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
        }
        if (! empty($f['department_id'])) {
            $query->where('department_id', $f['department_id']);
        }

        return $query;
    }

    private function count(string $model, string $dateColumn, Carbon $from, Carbon $to, array $f, ?callable $constraint = null): int
    {
        $query = $this->scoped($model, $f)->whereBetween($dateColumn, [$from, $to]);
        if ($constraint) {
            $constraint($query);
        }

        return $query->count();
    }

    private function sum(string $model, string $column, string $dateColumn, Carbon $from, Carbon $to, array $f): float
    {
        return (float) $this->scoped($model, $f)->whereBetween($dateColumn, [$from, $to])->sum($column);
    }

    private function avg(string $model, string $column, string $dateColumn, Carbon $from, Carbon $to, array $f, ?callable $constraint = null): ?float
    {
        $query = $this->scoped($model, $f)->whereBetween($dateColumn, [$from, $to])->whereNotNull($column);
        if ($constraint) {
            $constraint($query);
        }
        $value = $query->avg($column);

        return $value === null ? null : (float) $value;
    }

    private function distribution(string $model, string $column, string $dateColumn, array $f, int $limit = 12): array
    {
        $rows = $this->scoped($model, $f)
            ->whereBetween($dateColumn, [$f['from'], $f['to']])
            ->select($column, DB::raw('COUNT(*) as c'))
            ->groupBy($column)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => $r->{$column} ?: 'Unspecified')->all(),
            'data' => $rows->pluck('c')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function topBySite(string $model, string $dateColumn, array $f, ?callable $constraint = null): array
    {
        $query = $this->scoped($model, $f)
            ->whereBetween($dateColumn, [$f['from'], $f['to']])
            ->select('site_id', DB::raw('COUNT(*) as c'))
            ->groupBy('site_id')
            ->orderByDesc('c')
            ->limit(10);
        if ($constraint) {
            $constraint($query);
        }
        $rows = $query->get();

        $names = Site::whereIn('id', $rows->pluck('site_id')->filter())->pluck('name', 'id');

        return [
            'labels' => $rows->map(fn ($r) => $names[$r->site_id] ?? 'Unassigned')->all(),
            'data' => $rows->pluck('c')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function topByDepartment(string $model, string $dateColumn, array $f): array
    {
        $rows = $this->scoped($model, $f)
            ->whereBetween($dateColumn, [$f['from'], $f['to']])
            ->select('department_id', DB::raw('COUNT(*) as c'))
            ->groupBy('department_id')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        $names = Department::whereIn('id', $rows->pluck('department_id')->filter())->pluck('name', 'id');

        return [
            'labels' => $rows->map(fn ($r) => $names[$r->department_id] ?? 'Unassigned')->all(),
            'data' => $rows->pluck('c')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function avgBySite(string $model, string $column, string $dateColumn, array $f, ?callable $constraint = null): array
    {
        $query = $this->scoped($model, $f)
            ->whereBetween($dateColumn, [$f['from'], $f['to']])
            ->whereNotNull($column)
            ->select('site_id', DB::raw("AVG($column) as c"))
            ->groupBy('site_id')
            ->orderByDesc('c')
            ->limit(10);
        if ($constraint) {
            $constraint($query);
        }
        $rows = $query->get();

        $names = Site::whereIn('id', $rows->pluck('site_id')->filter())->pluck('name', 'id');

        return [
            'labels' => $rows->map(fn ($r) => $names[$r->site_id] ?? 'Unassigned')->all(),
            'data' => $rows->pluck('c')->map(fn ($v) => round((float) $v, 1))->all(),
        ];
    }

    private function monthlyCounts(string $model, string $dateColumn, int $year, array $f, ?callable $constraint = null): array
    {
        $query = $this->scoped($model, $f)
            ->whereYear($dateColumn, $year);
        if ($constraint) {
            $constraint($query);
        }

        $rows = $query->get([$dateColumn]);
        $buckets = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $month = (int) $row->{$dateColumn}?->format('n');
            if ($month >= 1 && $month <= 12) {
                $buckets[$month]++;
            }
        }

        return $buckets;
    }

    private function monthlyAverage(string $model, string $column, string $dateColumn, int $year, array $f, ?callable $constraint = null): array
    {
        $query = $this->scoped($model, $f)
            ->whereYear($dateColumn, $year)
            ->whereNotNull($column);
        if ($constraint) {
            $constraint($query);
        }

        $rows = $query->get([$dateColumn, $column]);
        $sums = array_fill(1, 12, 0.0);
        $counts = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $month = (int) $row->{$dateColumn}?->format('n');
            if ($month >= 1 && $month <= 12) {
                $sums[$month] += (float) $row->{$column};
                $counts[$month]++;
            }
        }

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[] = $counts[$m] > 0 ? round($sums[$m] / $counts[$m], 1) : 0;
        }

        return [
            'labels' => $this->monthLabels(),
            'series' => [['name' => $column, 'data' => $data, 'color' => '#1646A0']],
        ];
    }

    private function exposureHours(array $f): float
    {
        // 1. Cek dari SheManhour (manhours tervalidasi)
        $manhourQuery = \App\Models\SheManhour::query()->where('status', 'Validated');
        if (($f['scope_site_ids'] ?? null) !== null) {
            $manhourQuery->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
        }
        if (! empty($f['department_id'])) {
            $manhourQuery->where('department_id', $f['department_id']);
        }
        $manhourSum = (float) $manhourQuery->whereBetween('date', [$f['from']->toDateString(), $f['to']->toDateString()])->sum('manhours');
        if ($manhourSum > 0) {
            return $manhourSum;
        }

        // 2. Cek dari HseExposureHour
        $query = HseExposureHour::query();
        if (($f['scope_site_ids'] ?? null) !== null) {
            $query->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
        }
        if (! empty($f['department_id'])) {
            $query->where('department_id', $f['department_id']);
        }

        $fromYm = (int) $f['from']->format('Ym');
        $toYm = (int) $f['to']->format('Ym');

        $hseHours = (float) $query
            ->whereRaw('(period_year * 100 + period_month) >= ?', [$fromYm])
            ->whereRaw('(period_year * 100 + period_month) <= ?', [$toYm])
            ->sum('hours');
        if ($hseHours > 0) {
            return $hseHours;
        }

        // 3. Cek dari IndicatorDaily (MAN_HOURS)
        $manHoursInd = \App\Models\Indicator::where('code', 'MAN_HOURS')->first();
        if ($manHoursInd) {
            $indQuery = \App\Models\IndicatorDaily::query()->where('indicator_id', $manHoursInd->id);
            if (($f['scope_site_ids'] ?? null) !== null) {
                $indQuery->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
            }
            $indHours = (float) $indQuery->whereBetween('date', [$f['from']->toDateString(), $f['to']->toDateString()])->sum('value');
            if ($indHours > 0) {
                return $indHours;
            }
        }

        return 0.0;
    }

    private function daysWithoutLti(array $f): ?int
    {
        $query = HseIncident::query()->where('type', 'Lost Time Injury');
        if (($f['scope_site_ids'] ?? null) !== null) {
            $query->whereIn('site_id', $f['scope_site_ids'] ?: [-1]);
        }
        $last = $query->orderByDesc('occurred_at')->value('occurred_at');
        if (! $last) {
            return null;
        }

        return Carbon::parse($last)->startOfDay()->diffInDays(now(self::TZ)->startOfDay());
    }

    public function resolveTargetValue(string $key, array $f, $default = null): ?float
    {
        $year = (int) ($f['year'] ?? now()->year);
        $siteId = $f['site_id'] ?? null;

        // 1. Check SheKpiTarget for site + year
        if ($siteId) {
            $val = \App\Models\SheKpiTarget::where('kpi_key', $key)
                ->where('year', $year)
                ->where('site_id', $siteId)
                ->value('target_value');
            if ($val !== null) {
                return (float) $val;
            }
        }

        // 2. Check SheKpiTarget for global year
        $val = \App\Models\SheKpiTarget::where('kpi_key', $key)
            ->where('year', $year)
            ->whereNull('site_id')
            ->value('target_value');
        if ($val !== null) {
            return (float) $val;
        }

        // 3. Fallback to HseKpiTarget
        $hseTarget = HseKpiTarget::where('key', $key)->where('is_active', true)->first();
        if ($hseTarget && $hseTarget->target_value !== null) {
            return (float) $hseTarget->target_value;
        }

        return $default !== null ? (float) $default : null;
    }

    private function targets(): Collection
    {
        return HseKpiTarget::where('is_active', true)->get()->keyBy('key');
    }

    private function kpi(
        string $key,
        string $label,
        $value,
        $previous,
        string $goodDirection,
        $target,
        string $unit,
        ?string $href,
        string $formula,
        int $decimals = 0
    ): array {
        $change = null;
        $trend = 'flat';
        if ($previous !== null && (float) $previous != 0.0 && $value !== null) {
            $change = (((float) $value - (float) $previous) / abs((float) $previous)) * 100;
            $trend = $change > 0.05 ? 'up' : ($change < -0.05 ? 'down' : 'flat');
        }

        $status = 'neutral';
        if ($target !== null && $value !== null) {
            $value = (float) $value;
            $target = (float) $target;
            if ($goodDirection === 'higher') {
                $status = $value >= $target ? 'success' : ($value >= $target * 0.9 ? 'warning' : 'danger');
            } else {
                $status = $value <= $target ? 'success' : ($value <= $target * 1.1 || $target == 0.0 ? 'warning' : 'danger');
            }
        }

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'previous' => $previous,
            'change' => $change === null ? null : round($change, 1),
            'trend' => $trend,
            'good_direction' => $goodDirection,
            'target' => $target,
            'unit' => $unit,
            'status' => $status,
            'href' => $href,
            'formula' => $formula,
            'decimals' => $decimals,
            'is_good' => $change === null ? null : (($goodDirection === 'higher' && $change >= 0) || ($goodDirection === 'lower' && $change <= 0)),
        ];
    }

    private function alerts(array $f): array
    {
        $alerts = [];

        $critical = $this->count(HseIncident::class, 'occurred_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('severity', 'Critical')->where('status', 'Open'));
        if ($critical > 0) {
            $alerts[] = ['level' => 'danger', 'title' => $critical.' critical incident(s) masih Open', 'href' => route('hse.dashboard.incidents', $this->query($f))];
        }

        $overdue = $this->scoped(HseCorrectiveAction::class, $f)->whereNotIn('status', ['Closed'])->whereDate('due_date', '<', now(self::TZ)->toDateString())->count();
        if ($overdue > 0) {
            $alerts[] = ['level' => 'danger', 'title' => $overdue.' corrective action melewati due date', 'href' => route('hse.dashboard.actions', $this->query($f))];
        }

        $expiring = $this->scoped(HseTrainingRecord::class, $f)->where('status', 'Completed')->whereBetween('expires_at', [now(self::TZ)->toDateString(), now(self::TZ)->addDays(90)->toDateString()])->count();
        if ($expiring > 0) {
            $alerts[] = ['level' => 'warning', 'title' => $expiring.' sertifikasi akan kedaluwarsa dalam 90 hari', 'href' => route('hse.dashboard.training', $this->query($f))];
        }

        $permits = $this->scoped(HsePermit::class, $f)->whereDate('valid_to', '>=', now(self::TZ)->toDateString())->whereDate('valid_to', '<=', now(self::TZ)->addDays(30)->toDateString())->count();
        if ($permits > 0) {
            $alerts[] = ['level' => 'warning', 'title' => $permits.' permit akan kedaluwarsa dalam 30 hari', 'href' => route('hse.dashboard.risk', $this->query($f))];
        }

        $upcoming = $this->scoped(HseInspection::class, $f)->whereIn('status', ['Scheduled', 'In Progress'])->whereBetween('scheduled_date', [now(self::TZ)->toDateString(), now(self::TZ)->addDays(7)->toDateString()])->count();
        if ($upcoming > 0) {
            $alerts[] = ['level' => 'info', 'title' => $upcoming.' inspeksi dijadwalkan dalam 7 hari', 'href' => route('hse.dashboard.inspections', $this->query($f))];
        }

        if (empty($alerts)) {
            $alerts[] = ['level' => 'success', 'title' => 'Tidak ada alert kritis pada periode ini.', 'href' => null];
        }

        return $alerts;
    }

    private function recentIncidents(array $f, int $limit): array
    {
        return $this->scoped(HseIncident::class, $f)
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->with('site:id,code,name')
            ->orderByDesc('occurred_at')->limit($limit)->get()
            ->map(fn (HseIncident $i) => [
                'id' => $i->id,
                'date' => $i->occurred_at?->format('d M Y H:i'),
                'category' => $i->category,
                'type' => $i->type,
                'severity' => $i->severity,
                'site' => $i->site?->name ?? '—',
                'status' => $i->status,
            ])->all();
    }

    private function upcomingInspections(array $f, int $limit): array
    {
        return $this->scoped(HseInspection::class, $f)
            ->whereIn('status', ['Scheduled', 'In Progress'])
            ->whereDate('scheduled_date', '>=', now(self::TZ)->toDateString())
            ->orderBy('scheduled_date')->limit($limit)->with('site:id,code,name')->get()
            ->map(fn (HseInspection $i) => [
                'id' => $i->id,
                'title' => $i->title,
                'date' => $i->scheduled_date?->format('d M Y'),
                'site' => $i->site?->name ?? '—',
                'status' => $i->status,
            ])->all();
    }

    private function overdueActions(array $f, int $limit): array
    {
        return $this->scoped(HseCorrectiveAction::class, $f)
            ->whereNotIn('status', ['Closed'])
            ->whereDate('due_date', '<', now(self::TZ)->toDateString())
            ->orderBy('due_date')->limit($limit)->with('assignee:id,name')->get()
            ->map(fn (HseCorrectiveAction $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'due_date' => $a->due_date?->format('d M Y'),
                'pic' => $a->assignee?->name ?? '—',
                'priority' => $a->priority,
            ])->all();
    }

    private function complianceSummary(array $f): array
    {
        $total = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f);
        $closed = $this->count(HseFinding::class, 'created_at', $f['from'], $f['to'], $f, fn ($q) => $q->where('status', 'Closed'));
        $expired = $this->scoped(HsePermit::class, $f)->where(function ($q) {
            $q->where('status', 'Expired')->orWhereDate('valid_to', '<', now(self::TZ)->toDateString());
        })->count();

        return [
            'percentage' => $total > 0 ? round(($closed / $total) * 100, 1) : 100.0,
            'compliant' => $closed,
            'non_compliant' => max(0, $total - $closed),
            'expired' => $expired,
        ];
    }

    private function monthLabels(): array
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }

    private function query(array $f): array
    {
        return array_filter([
            'period' => $f['period'],
            'year' => $f['year'],
            'month' => $f['month'],
            'quarter' => $f['quarter'],
            'site_id' => $f['site_id'],
            'company_id' => $f['company_id'],
            'department_id' => $f['department_id'],
        ], fn ($v) => $v !== null && $v !== '');
    }
}
