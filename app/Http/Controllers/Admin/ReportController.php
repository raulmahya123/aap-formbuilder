<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    IndicatorDaily,
    IndicatorGroup,
    Site,
    Indicator,
    IndicatorValue
};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Halaman rekap utama.
     */
    public function report(Request $r)
    {
        $scope  = $r->string('scope')->lower()->value() ?: 'month'; // day|week|month|year
        $siteId = $r->input('site_id'); // null = semua site

        $now   = now();
        $year  = (int) $r->input('year',  $now->year);
        $month = (int) $r->input('month', $now->month);
        $date  = $r->input('date', $now->toDateString());
        $week  = (int) $r->input('week', (int)$now->isoWeek);

        [$startDate, $endDate, $periodLabel] = $this->resolveRange($scope, $year, $month, $week, $date);

        $sites  = Site::orderBy('code')->get();
        $groups = IndicatorGroup::with([
                        'indicators' => fn ($q) => $q->where('is_active', true)->orderBy('order_index')
                  ])->where('is_active', true)
                    ->orderBy('order_index')
                    ->get();

        // ===== Ambil nilai override bulanan dari IndicatorValue (kalau scope=month & site dipilih) =====
        $manualValues = collect();
        if ($scope === 'month' && $siteId) {
            $manualValues = IndicatorValue::query()
                ->where('year', $year)
                ->where('month', $month)
                ->where('site_id', $siteId)
                ->get()
                ->keyBy('indicator_id');
        }

        // ===== Agregasi (TOTAL / ON-TIME / LATE) per indikator dari IndicatorDaily =====
        $agg = IndicatorDaily::query()
            ->select([
                'indicator_id',
                DB::raw('SUM(value) AS total'),
                DB::raw('SUM(CASE WHEN is_late = 1 THEN value ELSE 0 END) AS late_total'),
                DB::raw('SUM(CASE WHEN is_late = 0 OR is_late IS NULL THEN value ELSE 0 END) AS ontime_total'),
            ])
            ->whereBetween('date', [$startDate, $endDate])
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->groupBy('indicator_id')
            ->get()
            ->keyBy('indicator_id');

        // ===== Susun payload $data per group + threshold per indikator (TERMASUK override) =====
        $data = [];
        foreach ($groups as $g) {
            foreach ($g->indicators as $ind) {
                $row     = $agg->get($ind->id);
                $total   = (float) ($row->total ?? 0);
                $ontime  = (float) ($row->ontime_total ?? 0);
                $late    = (float) ($row->late_total ?? 0);

                // kalau scope=month & site dipilih & ada IndicatorValue, pakai itu
                if ($scope === 'month' && $siteId) {
                    if ($manual = $manualValues->get($ind->id)) {
                        $total = (float) $manual->value;
                    }
                }

                // simpan threshold original (string/angka/null)
                $thrRaw = $ind->threshold;

                $data[$g->code][] = [
                    'indicator' => $ind,
                    'value'     => $total,
                    'total'     => $total,
                    'on_time'   => $ontime,
                    'late'      => $late,
                    'threshold' => $thrRaw, // jgn dipaksa float
                ];
            }
        }

        // ===== Stat cards (total keseluruhan periode) =====
        $sum = IndicatorDaily::query()
            ->selectRaw('
                SUM(value) AS total,
                SUM(CASE WHEN is_late = 1 THEN value ELSE 0 END) AS late_total,
                SUM(CASE WHEN is_late = 0 OR is_late IS NULL THEN value ELSE 0 END) AS ontime_total
            ')
            ->whereBetween('date', [$startDate, $endDate])
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->first();

        $totalLate   = (float) ($sum->late_total ?? 0);
        $totalOntime = (float) ($sum->ontime_total ?? 0);

        // ===== Charts per group (optional, tetap dari $data) =====
        $charts = [];
        foreach ($groups as $g) {
            $rows   = $data[$g->code] ?? [];
            $labels = [];
            $values = [];
            $units  = [];
            $allInt = true;
            $thrArr = [];

            foreach ($rows as $rrow) {
                $ind       = $rrow['indicator'];
                $labels[]  = $ind->name;
                $values[]  = (float) $rrow['total'];
                $units[]   = $ind->unit ?? '-';

                // parse threshold ke float hanya kalau numeric murni
                $thrRaw = $rrow['threshold'];
                $thrNum = null;
                if (is_numeric($thrRaw)) {
                    $thrNum = (float) $thrRaw;
                } elseif (is_string($thrRaw)) {
                    $s = trim($thrRaw);
                    $s = preg_replace('/[^0-9,.\-]/', '', $s);
                    $s = str_replace([','], ['.'], $s);
                    if (is_numeric($s)) $thrNum = (float) $s;
                }
                $thrArr[] = $thrNum;

                if (($ind->data_type ?? 'int') !== 'int') $allInt = false;
            }

            $hasAnyThr = collect($thrArr)->filter(fn ($v) => $v !== null)->isNotEmpty();

            $charts[$g->code] = array_filter([
                'group_name'    => $g->name,
                'labels'        => $labels,
                'values'        => $values,
                'units'         => $units,
                'all_int'       => $allInt,
                'dataset_label' => 'Total Periode Ini',
                'thresholds'    => $hasAnyThr ? $thrArr : null,
            ], fn ($v) => $v !== null);
        }

        $period = $periodLabel;

        return view('admin.reports.aggregate', compact(
            'sites', 'siteId', 'scope', 'year', 'month', 'week', 'date',
            'data', 'period', 'groups', 'charts',
            'totalOntime', 'totalLate'
        ));
    }

    /**
     * Hitung range tanggal berdasarkan scope.
     */
    private function resolveRange(string $scope, int $year, int $month, int $week, string $date): array
    {
        switch ($scope) {
            case 'day':
                $d = Carbon::parse($date)->startOfDay();
                return [$d->toDateString(), $d->copy()->endOfDay()->toDateString(), $d->isoFormat('D MMMM YYYY')];

            case 'week':
                $start = Carbon::now()->setISODate($year, max(1, min(53, $week)))->startOfWeek(Carbon::MONDAY);
                $end   = $start->copy()->endOfWeek(Carbon::SUNDAY);
                $label = "Minggu {$week} — " . $start->isoFormat('D MMM') . ' s.d ' . $end->isoFormat('D MMM YYYY');
                return [$start->toDateString(), $end->toDateString(), $label];

            case 'year':
                $start = Carbon::create($year, 1, 1)->startOfDay();
                $end   = Carbon::create($year, 12, 31)->endOfDay();
                return [$start->toDateString(), $end->toDateString(), $start->isoFormat('YYYY')];

            case 'month':
            default:
                $start = Carbon::create($year, $month, 1)->startOfDay();
                $end   = $start->copy()->endOfMonth()->endOfDay();
                return [$start->toDateString(), $end->toDateString(), $start->isoFormat('MMMM YYYY')];
        }
    }

    /**
     * Form edit / override total agregat (dipanggil dari tombol "Edit Total").
     * Untuk saat ini: hanya scope=month dan site wajib dipilih.
     */
    public function editTotal(Request $request)
    {
        $user = $request->user();

        // cek super_admin sama seperti di Blade
        $isSuperAdmin = $user && (
            (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) ||
            (($user->role ?? $user->role_key ?? null) === 'super_admin')
        );

        abort_unless($isSuperAdmin, 403, 'Hanya super admin yang boleh mengedit total.');

        $scope = $request->string('scope')->lower()->value() ?: 'month';
        abort_unless($scope === 'month', 404, 'Override total sementara hanya untuk scope bulanan.');

        $now    = now();
        $year   = (int) $request->input('year',  $now->year);
        $month  = (int) $request->input('month', $now->month);
        $date   = $request->input('date', $now->toDateString());
        $week   = (int) $request->input('week', (int)$now->isoWeek);

        [, , $periodLabel] = $this->resolveRange($scope, $year, $month, $week, $date);

        // context indikator + site
        $indicatorId = (int) $request->input('indicator_id');
        $groupCode   = $request->input('group_code');
        $siteId      = (int) $request->input('site_id');

        abort_unless($siteId, 400, 'Pilih site terlebih dahulu untuk override total.');

        $indicator = $indicatorId ? Indicator::find($indicatorId) : null;
        $site      = $siteId ? Site::find($siteId) : null;

        // ambil nilai existing dari IndicatorValue
        $iv = IndicatorValue::where('indicator_id', $indicatorId)
                ->where('site_id', $siteId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

        $existingTotal = $iv?->value ?? 0;

        return view('admin.reports.edit_total', [
            'indicator'      => $indicator,
            'site'           => $site,
            'groupCode'      => $groupCode,
            'scope'          => $scope,
            'date'           => $date,
            'week'           => $week,
            'month'          => $month,
            'year'           => $year,
            'siteId'         => $siteId,
            'periodLabel'    => $periodLabel,
            'existingTotal'  => $existingTotal,
        ]);
    }

    /**
     * Simpan nilai total override ke IndicatorValue.
     */
    public function updateTotal(Request $request)
    {
        $user = $request->user();

        $isSuperAdmin = $user && (
            (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) ||
            (($user->role ?? $user->role_key ?? null) === 'super_admin')
        );

        abort_unless($isSuperAdmin, 403, 'Hanya super admin yang boleh mengedit total.');

        $data = $request->validate([
            'indicator_id' => 'required|integer|exists:indicators,id',
            'group_code'   => 'required|string',
            'scope'        => 'required|string|in:month', // sementara hanya month
            'date'         => 'nullable|date',
            'week'         => 'nullable|integer',
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer',
            'site_id'      => 'required|integer|exists:sites,id',
            'total'        => 'required|numeric',
        ]);

        // Simpan / update ke IndicatorValue
        $iv = IndicatorValue::updateOrCreate(
            [
                'indicator_id' => $data['indicator_id'],
                'site_id'      => $data['site_id'],
                'year'         => $data['year'],
                'month'        => $data['month'],
            ],
            [
                'value'        => $data['total'],
            ]
        );

        return redirect()
            ->route('admin.reports.monthly', [
                'scope'   => $data['scope'],
                'date'    => $data['date'],
                'week'    => $data['week'],
                'month'   => $data['month'],
                'year'    => $data['year'],
                'site_id' => $data['site_id'],
            ])
            ->with('status', 'Total indikator berhasil di-override.');
    }
}
