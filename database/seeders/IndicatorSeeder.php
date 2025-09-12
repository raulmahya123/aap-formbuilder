<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{
    IndicatorGroup,
    Indicator,
    IndicatorDaily,
    IndicatorValue,
    Site
};

class IndicatorSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Buat minimal 1 site (supaya FK site_id valid)
            $site = Site::firstOrCreate(
                ['code' => 'HO'],
                ['name' => 'Head Office']
            );

            // 2. Buat Indicator Groups
            $groups = [
                ['name' => 'Lagging Indicators', 'code' => 'LAG', 'order_index' => 1, 'is_active' => 1],
                ['name' => 'Leading Indicators', 'code' => 'LEAD', 'order_index' => 2, 'is_active' => 1],
            ];

            foreach ($groups as $g) {
                IndicatorGroup::updateOrCreate(['code' => $g['code']], $g);
            }

            $lagGroup  = IndicatorGroup::where('code', 'LAG')->first();
            $leadGroup = IndicatorGroup::where('code', 'LEAD')->first();

            // 3. Buat Indicators
            $indicators = [
                // Lagging
                [
                    'indicator_group_id' => $lagGroup->id,
                    'name'        => 'Fatality',
                    'code'        => 'FATALITY',
                    'data_type'   => 'int',
                    'agg'         => 'sum',
                    'unit'        => 'kasus',
                    'order_index' => 1,
                    'is_derived'  => 0,
                    'formula'     => null,
                    'is_active'   => 1,
                ],
                [
                    'indicator_group_id' => $lagGroup->id,
                    'name'        => 'Lost Time Injury',
                    'code'        => 'LTI',
                    'data_type'   => 'int',
                    'agg'         => 'sum',
                    'unit'        => 'kasus',
                    'order_index' => 2,
                    'is_derived'  => 0,
                    'formula'     => null,
                    'is_active'   => 1,
                ],
                [
                    'indicator_group_id' => $lagGroup->id,
                    'name'        => 'LTI Severity Rate',
                    'code'        => 'LTI_SR',
                    'data_type'   => 'rate',
                    'agg'         => 'sum',
                    'unit'        => null,
                    'order_index' => 3,
                    'is_derived'  => 1,
                    'formula'     => 'LTI / MAN_HOURS * 1e6',
                    'is_active'   => 1,
                ],
                // Leading
                [
                    'indicator_group_id' => $leadGroup->id,
                    'name'        => 'Safety Observation',
                    'code'        => 'OBS',
                    'data_type'   => 'int',
                    'agg'         => 'sum',
                    'unit'        => 'laporan',
                    'order_index' => 1,
                    'is_derived'  => 0,
                    'formula'     => null,
                    'is_active'   => 1,
                ],
                [
                    'indicator_group_id' => $leadGroup->id,
                    'name'        => 'Safety Training Hours',
                    'code'        => 'TRAIN_HRS',
                    'data_type'   => 'int', // ubah float → int kalau enum tidak support
                    'agg'         => 'sum',
                    'unit'        => 'jam',
                    'order_index' => 2,
                    'is_derived'  => 0,
                    'formula'     => null,
                    'is_active'   => 1,
                ],
            ];

            foreach ($indicators as $i) {
                Indicator::updateOrCreate(['code' => $i['code']], $i);
            }

            // 4. Seed contoh Daily Values
            $obs = Indicator::where('code', 'OBS')->first();
            if ($obs) {
                IndicatorDaily::updateOrCreate(
                    [
                        'site_id'      => $site->id,
                        'indicator_id' => $obs->id,
                        'date'         => now()->toDateString(),
                    ],
                    [
                        'value' => 5,
                        'note'  => 'Observasi keselamatan harian',
                    ]
                );
            }

            // 5. Seed contoh Monthly Values
            $train = Indicator::where('code', 'TRAIN_HRS')->first();
            if ($train) {
                IndicatorValue::updateOrCreate(
                    [
                        'site_id'      => $site->id,
                        'indicator_id' => $train->id,
                        'year'         => now()->year,
                        'month'        => now()->month,
                    ],
                    [
                        'value' => 120,
                    ]
                );
            }
        });
    }
}
