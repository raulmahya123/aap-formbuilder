<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // =========================
    // Entry point generik
    // =========================
    public function report(Request $r)
    {
        $scope = $r->string('scope')->lower()->value() ?: 'month'; // day|week|month|year
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

        // Form state untuk view
        $period   = $periodLabel;
        return view('admin.reports.aggregate', compact(
            'sites','siteId','scope','year','month','week','date',
            'data','period','groups','charts'
        ));
    }

    // Backward compatibility (route lama)
    public function monthly(Request $r)
    {
        // delegasi ke report() dengan scope=month
        $r->merge(['scope' => 'month']);
        return $this->report($r);
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

            case 'week':
                // ISO week (Senin–Minggu)
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
        uksort($vars, fn($a, $b) => strlen($b) <=> strlen($a)); // panjang dulu
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
}
