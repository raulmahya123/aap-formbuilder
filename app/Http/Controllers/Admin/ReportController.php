<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{IndicatorDaily, IndicatorGroup, Site};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
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

        // ===== Agregasi (TOTAL / ON-TIME / LATE) per indikator =====
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

        // ===== Susun payload $data per group + threshold per indikator =====
        $data = [];
        foreach ($groups as $g) {
            foreach ($g->indicators as $ind) {
                $row     = $agg->get($ind->id);
                $total   = (float) ($row->total ?? 0);
                $ontime  = (float) ($row->ontime_total ?? 0);
                $late    = (float) ($row->late_total ?? 0);

                // langsung ikut dari model (float|null sesuai casts di model)
                $thr = ($ind->threshold !== null && $ind->threshold !== '') ? (float) $ind->threshold : null;

                $data[$g->code][] = [
                    'indicator' => $ind,
                    'value'     => $total,   // alias
                    'total'     => $total,
                    'on_time'   => $ontime,
                    'late'      => $late,
                    'threshold' => $thr,
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

        // ===== Charts per group (pakai total) + threshold sejajar (opsional di view) =====
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
                $thrArr[]  = $rrow['threshold']; // float|null
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
}
