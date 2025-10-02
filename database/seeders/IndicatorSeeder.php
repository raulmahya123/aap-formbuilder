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
            // 1) Minimal 1 site agar FK valid
            $site = Site::firstOrCreate(['code' => 'HO'], ['name' => 'Head Office']);

            // 2) Groups
            $groups = [
                ['name' => 'Lagging Indicators',           'code' => 'LAG',  'order_index' => 1, 'is_active' => 1],
                ['name' => 'Leading Indicators',           'code' => 'LEAD', 'order_index' => 2, 'is_active' => 1],
                ['name' => 'Deskripsi (Base Metrics)',     'code' => 'BASE', 'order_index' => 3, 'is_active' => 1],
            ];
            foreach ($groups as $g) {
                IndicatorGroup::updateOrCreate(['code' => $g['code']], $g);
            }

            $gLag  = IndicatorGroup::where('code', 'LAG')->first();
            $gLead = IndicatorGroup::where('code', 'LEAD')->first();
            $gBase = IndicatorGroup::where('code', 'BASE')->first();

            // 3) Indicators (dengan threshold campuran)
            $indicators = [
                // ===== BASE / DESKRIPSI =====
                [
                    'indicator_group_id' => $gBase->id,
                    'name'        => 'Man Power',
                    'code' => 'MAN_POWER',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'orang',
                    'order_index' => 1,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 100, // angka polos
                ],
                [
                    'indicator_group_id' => $gBase->id,
                    'name'        => 'Man Hours',
                    'code' => 'MAN_HOURS',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'jam',
                    'order_index' => 2,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => null, // kosong
                ],
                [
                    'indicator_group_id' => $gBase->id,
                    'name'        => 'Lost Days',
                    'code' => 'LOST_DAYS',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'hari',
                    'order_index' => 3,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 5,
                ],

                // ===== LAGGING =====
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'Fatality',
                    'code' => 'FATALITY',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 1,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 0, // target 0
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'LTI (Lost Time Injury)',
                    'code' => 'LTI',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 2,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => '2%', // persen string
                ],
                // SR umumnya = (Lost Days / Man Hours) * 1e6
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'LTI SR (Severity Rate)',
                    'code' => 'LTI_SR',
                    'data_type'   => 'rate',
                    'agg' => 'sum',
                    'unit' => null,
                    'order_index' => 3,
                    'is_derived' => 1,
                    'formula'     => 'LOST_DAYS / MAN_HOURS * 1e6',
                    'is_active' => 1,
                    'threshold'   => '10%', // persen string
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'Injury Non LTI',
                    'code' => 'INJURY_NON_LTI',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 4,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 3,
                ],
                // FR = Frequency Rate
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'Injury Non LTI FR',
                    'code' => 'INJURY_NON_LTI_FR',
                    'data_type'   => 'rate',
                    'agg' => 'sum',
                    'unit' => null,
                    'order_index' => 5,
                    'is_derived' => 1,
                    'formula'     => 'INJURY_NON_LTI / MAN_HOURS * 1e6',
                    'is_active' => 1,
                    'threshold'   => 1.5, // angka desimal
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'PD (Property Damage)',
                    'code' => 'PD',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 6,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 1,
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'PDFR (Property Damage Frequency Rate)',
                    'code' => 'PDFR',
                    'data_type'   => 'rate',
                    'agg' => 'sum',
                    'unit' => null,
                    'order_index' => 7,
                    'is_derived' => 1,
                    'formula'     => 'PD / MAN_HOURS * 1e6',
                    'is_active' => 1,
                    'threshold'   => 0.8,
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'PD Cost',
                    'code' => 'PD_COST',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'rupiah',
                    'order_index' => 8,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 'Rp 1.500.000', // mata uang IDR
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'PAK (Penyakit Akibat Kerja)',
                    'code' => 'PAK',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 9,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 0,
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'KAPTK (Kejadian Akibat Penyakit Tenaga Kerja)',
                    'code' => 'KAPTK',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 10,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 0,
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'Enviro Accident',
                    'code' => 'ENV_ACCIDENT',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 11,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 2,
                ],
                [
                    'indicator_group_id' => $gLag->id,
                    'name'        => 'Near Miss',
                    'code' => 'NEAR_MISS',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kasus',
                    'order_index' => 12,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 10,
                ],

                // ===== LEADING =====
                [
                    'indicator_group_id' => $gLead->id,
                    'name'        => 'Safety Accountability Program (SAP)',
                    'code' => 'SAP',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kegiatan',
                    'order_index' => 1,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 'IDR 10.000', // contoh format IDR lain
                ],
                [
                    'indicator_group_id' => $gLead->id,
                    'name'        => 'KTA',
                    'code' => 'KTA',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kegiatan',
                    'order_index' => 2,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 75,
                ],
                [
                    'indicator_group_id' => $gLead->id,
                    'name'        => 'TTA',
                    'code' => 'TTA',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'kegiatan',
                    'order_index' => 3,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 80,
                ],
                [
                    'indicator_group_id' => $gLead->id,
                    'name'        => 'Inspeksi',
                    'code' => 'INSPEKSI',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'temuan',
                    'order_index' => 4,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => '$100', // dolar
                ],
                [
                    'indicator_group_id' => $gLead->id,
                    'name'        => 'Planned Task Observation (PTO)',
                    'code' => 'PTO',
                    'data_type'   => 'int',
                    'agg' => 'sum',
                    'unit' => 'observasi',
                    'order_index' => 5,
                    'is_derived' => 0,
                    'formula' => null,
                    'is_active' => 1,
                    'threshold'   => 50,
                ],
            ];

            foreach ($indicators as $i) {
                Indicator::updateOrCreate(['code' => $i['code']], $i);
            }

            // 4) Contoh seed harian (opsional)
            if ($obs = Indicator::where('code', 'INSPEKSI')->first()) {
                IndicatorDaily::updateOrCreate(
                    ['site_id' => $site->id, 'indicator_id' => $obs->id, 'date' => now()->toDateString()],
                    ['value' => 3, 'note' => 'Inspeksi rutin harian']
                );
            }

            // 5) Contoh seed bulanan (opsional)
            if ($train = Indicator::where('code', 'PTO')->first()) {
                IndicatorValue::updateOrCreate(
                    ['site_id' => $site->id, 'indicator_id' => $train->id, 'year' => now()->year, 'month' => now()->month],
                    ['value' => 25]
                );
            }
        });
    }
}
