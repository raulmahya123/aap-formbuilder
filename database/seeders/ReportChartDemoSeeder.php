<?php

namespace Database\Seeders;

use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportChartDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(IndicatorSeeder::class);

        DB::transaction(function () {
            $sites = collect([
                Site::updateOrCreate(
                    ['code' => 'HO'],
                    ['name' => 'Head Office', 'description' => 'Demo report site']
                ),
                Site::updateOrCreate(
                    ['code' => 'PIT'],
                    ['name' => 'Pit Demo', 'description' => 'Demo mining operation site']
                ),
            ]);

            $indicators = Indicator::query()
                ->where('is_active', true)
                ->get()
                ->keyBy('code');

            if ($indicators->isEmpty()) {
                return;
            }

            $start = now()->startOfMonth();
            $end = now()->endOfMonth();

            foreach ($sites as $siteIndex => $site) {
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $day = (int) $date->day;
                    $weekday = (int) $date->dayOfWeekIso;
                    $isWeekend = $weekday >= 6;
                    $siteFactor = $siteIndex + 1;

                    $values = [
                        'MAN_POWER' => 85 + ($siteFactor * 8) + (($day % 6) * 2),
                        'MAN_HOURS' => $isWeekend
                            ? 520 + ($siteFactor * 70) + (($day % 4) * 25)
                            : 760 + ($siteFactor * 95) + (($day % 5) * 35),
                        'LOST_DAYS' => in_array($day, [6, 18], true) && $site->code === 'PIT' ? 1 : 0,
                        'FATALITY' => 0,
                        'LTI' => $day === 18 && $site->code === 'PIT' ? 1 : 0,
                        'INJURY_NON_LTI' => in_array($day, [4, 11, 21], true) ? 1 : 0,
                        'PD' => in_array($day, [9, 25], true) && $site->code === 'HO' ? 1 : 0,
                        'PD_COST' => in_array($day, [9, 25], true) && $site->code === 'HO' ? 850000 : 0,
                        'PAK' => 0,
                        'KAPTK' => 0,
                        'ENV_ACCIDENT' => $day === 22 && $site->code === 'PIT' ? 1 : 0,
                        'NEAR_MISS' => ($day % 3) + $siteFactor,
                        'SAP' => 2 + ($day % 4) + $siteFactor,
                        'KTA' => 3 + ($day % 5) + $siteFactor,
                        'TTA' => 2 + ($day % 4),
                        'INSPEKSI' => 4 + ($day % 6) + $siteFactor,
                        'PTO' => 2 + ($day % 3) + $siteFactor,
                    ];

                    foreach ($values as $code => $value) {
                        $indicator = $indicators->get($code);
                        if (! $indicator) {
                            continue;
                        }

                        IndicatorDaily::updateOrCreate(
                            [
                                'site_id' => $site->id,
                                'indicator_id' => $indicator->id,
                                'date' => $date->toDateString(),
                            ],
                            [
                                'value' => $value,
                                'note' => 'Demo data grafik ' . $date->isoFormat('MMMM YYYY'),
                                'shift' => $day % 2 === 0 ? 1 : 2,
                                'input_at' => $date->copy()->setTime(8 + ($day % 8), 15),
                                'is_late' => $day % 7 === 0,
                            ]
                        );
                    }
                }
            }
        });
    }
}
