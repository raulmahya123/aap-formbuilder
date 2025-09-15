<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // =========================
    // Entry point generik
    // =========================
    public function report(Request $r)
    {
        $scope  = $r->string('scope')->lower()->value() ?: 'month'; // day|week|month|year
        $siteId = $r->input('site_id'); // null = semua site

        // Normalisasi parameter tanggal berdasar scope
        $now   = now();
        $year  = (int) $r->input('year',  $now->year);
        $month = (int) $r->input('month', $now->month);
        $date  = $r->input('date', $now->toDateString());  // untuk scope=day
        $week  = (int) $r->input('week', (int) $now->isoWeek); // untuk scope=week (ISO week)

        // Tentukan start-end & label periode
        [$startDate, $endDate, $periodLabel] = $this->resolveRange($scope, $year, $month, $week, $date);

        // Ambil master data
        $sites  = Site::orderBy('code')->get();
        $groups = IndicatorGroup::with(['indicators' => fn($q) => $q->where('is_active', true)->orderBy('order_index')])
            ->where('is_active', true)->orderBy('order_index')->get();

        // Hitung nilai tiap indikator pada rentang
        $data = [];
        foreach ($groups as $g) {
            foreach ($g->indicators as $ind) {
                $data[$g->code][] = [
                    'indicator' => $ind,
                    'value'     => $this->calcValueForRange($siteId ? (int)$siteId : null, $startDate, $endDate, $ind),
                ];
            }
        }

        // Siapkan payload grafik per grup
        $charts = [];
        foreach ($groups as $g) {
            $rows   = $data[$g->code] ?? [];
            $labels = [];
            $values = [];
            $units  = [];
            $allInt = true;

            foreach ($rows as $row) {
                /** @var \App\Models\Indicator $ind */
                $ind = $row['indicator'];
                $labels[] = $ind->name;
                $values[] = (float) $row['value'];
                $units[]  = $ind->unit ?? '-';
                if (($ind->data_type ?? 'int') !== 'int') $allInt = false;
            }

            $charts[$g->code] = [
                'group_name'    => $g->name,
                'labels'        => $labels,
                'values'        => $values,
                'units'         => $units,
                'all_int'       => $allInt,
                'dataset_label' => 'Total Periode Ini',
            ];
        }

        // ===== Trend series sesuai scope (day/week/month => harian; year => bulanan)
        [$trendLabels, $trendValues, $trendLabel] = $this->trendSeries($scope, $siteId ? (int)$siteId : null, $startDate, $endDate);

        // Form state untuk view
        $period = $periodLabel;

        return view('admin.reports.aggregate', compact(
            'sites','siteId','scope','year','month','week','date',
            'data','period','groups','charts',
            'trendLabels','trendValues','trendLabel'
        ));
    }
    // =========================
    // Helpers: Range resolver
    // =========================
    private function resolveRange(string $scope, int $year, int $month, int $week, string $date): array
    {
        switch ($scope) {
            case 'day':
                $d = Carbon::parse($date)->startOfDay();
                return [$d->toDateString(), $d->copy()->endOfDay()->toDateString(), $d->isoFormat('D MMMM YYYY')];

            case 'week': // ISO week (Mon–Sun)
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

    // =========================
    // Helpers: Kalkulasi nilai
    // =========================
    private function calcValueForRange(?int $siteId, string $startDate, string $endDate, Indicator $ind): float
    {
        if (!$ind->is_derived) {
            return $this->aggregateRange($siteId, $ind->id, $startDate, $endDate, $ind->agg);
        }

        $vars = $this->collectVarsFromFormulaRange($siteId, $startDate, $endDate, (string) $ind->formula);
        $expr = $this->substituteFormula((string) $ind->formula, $vars);
        return $this->safeEval($expr);
    }

    private function aggregateRange(?int $siteId, int $indicatorId, string $startDate, string $endDate, string $agg = 'sum'): float
    {
        $q = IndicatorDaily::query()
            ->where('indicator_id', $indicatorId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($siteId) $q->where('site_id', $siteId);

        return match ($agg) {
            'avg' => (float) $q->avg('value'),
            'max' => (float) $q->max('value'),
            'min' => (float) $q->min('value'),
            default => (float) $q->sum('value'),
        };
    }

    private function collectVarsFromFormulaRange(?int $siteId, string $startDate, string $endDate, string $formula): array
    {
        preg_match_all('/\b[A-Z][A-Z0-9_]*\b/', $formula, $m);
        $codes = array_unique($m[0] ?? []);

        $vars = [];
        foreach ($codes as $code) {
            $base = Indicator::where('code', $code)->first();
            $vars[$code] = $base ? $this->aggregateRange($siteId, $base->id, $startDate, $endDate, $base->agg) : 0.0;
        }
        return $vars;
    }

    private function substituteFormula(string $formula, array $vars): string
    {
        uksort($vars, fn($a, $b) => strlen($b) <=> strlen($a));
        $expr = $formula;
        foreach ($vars as $code => $val) {
            $expr = preg_replace('/\b' . preg_quote($code, '/') . '\b/', (string) ($val ?: 0), $expr);
        }
        return $expr ?? '';
    }

    private function safeEval(string $expr): float
    {
        $expr = trim(preg_replace('/\s+/', '', $expr));
        if ($expr === '') return 0.0;
        if (!preg_match('/^[0-9eE\.\+\-\*\/\(\)]+$/', $expr)) return 0.0;
        if (preg_match('/[\+\*\/]{2,}/', $expr)) return 0.0;

        try { /** @noinspection PhpEvalInspection */ return (float) eval("return (float)($expr);"); }
        catch (\Throwable $e) { return 0.0; }
    }

    // =========================
    // Trend builder
    // =========================
    /**
     * @return array{0: array, 1: array, 2: string} [labels, values, datasetLabel]
     */
    private function trendSeries(string $scope, ?int $siteId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        // day/week/month => granularitas harian; year => bulanan
        if (in_array($scope, ['day','week','month'], true)) {
            // ambil semua total per tanggal
            $q = IndicatorDaily::query()
                ->selectRaw('date, SUM(value) as total')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->when($siteId, fn($qq)=>$qq->where('site_id', $siteId))
                ->groupBy('date')
                ->orderBy('date');

            $rows = $q->get()->keyBy('date');

            $labels = [];
            $values = [];
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $d = $cursor->toDateString();
                $labels[] = $cursor->isoFormat('D MMM'); // contoh: 5 Sep
                $values[] = (float) ($rows[$d]->total ?? 0);
                $cursor->addDay();
            }
            $label = 'Total Harian';
            return [$labels, $values, $label];
        }

        // year => per bulan
        $q = IndicatorDaily::query()
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as ym, SUM(value) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($siteId, fn($qq)=>$qq->where('site_id', $siteId))
            ->groupBy('ym')
            ->orderBy('ym');

        $rows = $q->get()->keyBy('ym');

        $labels = [];
        $values = [];
        for ($m=1; $m<=12; $m++) {
            $ym = sprintf('%04d-%02d', (int)$start->format('Y'), $m);
            $labels[] = Carbon::create((int)$start->format('Y'), $m, 1)->isoFormat('MMM');
            $values[] = (float) ($rows[$ym]->total ?? 0);
        }
        $label = 'Total Bulanan';
        return [$labels, $values, $label];
    }
}
