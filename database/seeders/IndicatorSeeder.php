<?php

namespace Database\Seeders;

use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\IndicatorValue;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndicatorSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $site = Site::firstOrCreate(['code' => 'HO'], ['name' => 'Head Office']);

            $groups = [
                ['name' => 'Fatality & LTI Indicator', 'code' => 'FATAL_LTI', 'order_index' => 1, 'is_active' => true],
                ['name' => 'Lag Indicator', 'code' => 'LAG', 'order_index' => 2, 'is_active' => true],
                ['name' => 'Lead Indicator', 'code' => 'LEAD', 'order_index' => 3, 'is_active' => true],
                ['name' => 'Deskripsi (Base Metrics)', 'code' => 'BASE', 'order_index' => 4, 'is_active' => true],
            ];

            foreach ($groups as $group) {
                IndicatorGroup::updateOrCreate(['code' => $group['code']], $group);
            }

            $groupIds = IndicatorGroup::whereIn('code', collect($groups)->pluck('code'))
                ->pluck('id', 'code');

            $indicators = [
                // Base metrics for formulas.
                ['BASE', 'Man Hours', 'MAN_HOURS', 'int', 'jam', 1, false, null, null, null],
                ['BASE', 'Lost Days', 'LOST_DAYS', 'int', 'hari', 2, false, null, null, null],
                ['BASE', 'Jumlah Karyawan', 'MAN_POWER', 'int', 'orang', 3, false, null, null, null],
                ['BASE', 'Total Hari Kerja', 'WORK_DAYS', 'int', 'hari', 4, false, null, null, null],

                // Fatality & LTI Indicator.
                ['FATAL_LTI', 'Fatality', 'FATALITY', 'int', 'kasus', 1, false, null, '0', 33],
                ['FATAL_LTI', 'LTI', 'LTI', 'int', 'kasus', 2, false, null, '0', 33],
                ['FATAL_LTI', 'LTI SR', 'LTI_SR', 'rate', null, 3, true, 'LOST_DAYS / MAN_HOURS * 1e6', '0', 33],

                // Lag Indicator.
                ['LAG', 'Injury Non LTI FR', 'INJURY_NON_LTI_FR', 'rate', null, 1, true, 'INJURY_NON_LTI / MAN_HOURS * 1e6', '1.11', 20],
                ['LAG', 'PDFR', 'PDFR', 'rate', null, 2, true, 'PD / MAN_HOURS * 1e6', '6.91', 20],
                ['LAG', 'PD Cost', 'PD_COST', 'currency', 'Rp', 3, false, null, '$ 5,000', 10],
                ['LAG', 'PAK', 'PAK', 'int', 'kasus', 4, false, null, '0', 10],
                ['LAG', 'KAPTK', 'KAPTK', 'int', 'kasus', 5, false, null, '0', 10],
                ['LAG', 'Rasio Kelaikan Kerja (RKK)', 'RKK', 'rate', '%', 6, true, 'FIT_TO_WORK / MAN_POWER * 100', '100%', 10],
                ['LAG', 'Absence Severity Rate (ASR)', 'ASR', 'rate', null, 7, true, 'ABSENCE_DAYS / MAN_HOURS * 1e6', '300', 5],
                ['LAG', 'Morbidity Frequency Rate (MFR)', 'MFR', 'rate', null, 8, true, 'MORBIDITY_CASE / MAN_HOURS * 1e6', '400', 5],
                ['LAG', 'Enviro Accident', 'ENV_ACCIDENT', 'int', 'kasus', 9, false, null, '0', 10],

                // Inputs for derived indicators.
                ['BASE', 'Injury Non LTI', 'INJURY_NON_LTI', 'int', 'kasus', 5, false, null, null, null],
                ['BASE', 'Property Damage', 'PD', 'int', 'kasus', 6, false, null, null, null],
                ['BASE', 'Fit To Work', 'FIT_TO_WORK', 'int', 'orang', 7, false, null, null, null],
                ['BASE', 'Absence Days', 'ABSENCE_DAYS', 'int', 'hari', 8, false, null, null, null],
                ['BASE', 'Morbidity Case', 'MORBIDITY_CASE', 'int', 'kasus', 9, false, null, null, null],

                // Lead Indicator.
                ['LEAD', 'SHE Accountability Program', 'SHE_ACCOUNTABILITY_PROGRAM', 'rate', '%', 1, false, null, '100%', 20],
                ['LEAD', 'Hazard Report', 'HAZARD_REPORT', 'rate', '%', 2, false, null, '100%', 20],
                ['LEAD', 'Tindak Lanjut PICA', 'TINDAK_LANJUT_PICA', 'rate', '%', 3, false, null, '100%', 15],
                ['LEAD', 'Legal Compliance', 'LEGAL_COMPLIANCE', 'rate', '%', 4, false, null, '100%', 5],
                ['LEAD', 'Implementasi Program', 'IMPLEMENTASI_PROGRAM', 'rate', '%', 5, false, null, '100%', 10],
                ['LEAD', 'Training SHE', 'TRAINING_SHE', 'rate', '%', 6, false, null, '100%', 10],
                ['LEAD', 'Audit SMKP Score', 'AUDIT_SMKP_SCORE', 'rate', '%', 7, false, null, '65%', 20],
            ];

            foreach ($indicators as [$groupCode, $name, $code, $dataType, $unit, $order, $isDerived, $formula, $threshold, $weight]) {
                Indicator::updateOrCreate(
                    ['code' => $code],
                    [
                        'indicator_group_id' => $groupIds[$groupCode],
                        'name' => $name,
                        'data_type' => $dataType,
                        'agg' => 'sum',
                        'unit' => $unit,
                        'order_index' => $order,
                        'is_derived' => $isDerived,
                        'formula' => $formula,
                        'is_active' => true,
                        'threshold' => $threshold,
                        'weight' => $weight,
                    ]
                );
            }

            Indicator::whereIn('indicator_group_id', $groupIds->values())
                ->whereNotIn('code', collect($indicators)->pluck(2)->all())
                ->delete();

            $dailyDefaults = [
                'MAN_HOURS' => 100000,
                'LOST_DAYS' => 0,
                'MAN_POWER' => 100,
                'WORK_DAYS' => 26,
                'FATALITY' => 0,
                'LTI' => 0,
                'INJURY_NON_LTI' => 0,
                'PD' => 0,
                'PD_COST' => 0,
                'PAK' => 0,
                'KAPTK' => 0,
                'FIT_TO_WORK' => 100,
                'ABSENCE_DAYS' => 0,
                'MORBIDITY_CASE' => 0,
                'ENV_ACCIDENT' => 0,
                'SHE_ACCOUNTABILITY_PROGRAM' => 100,
                'HAZARD_REPORT' => 100,
                'TINDAK_LANJUT_PICA' => 100,
                'LEGAL_COMPLIANCE' => 100,
                'IMPLEMENTASI_PROGRAM' => 100,
                'TRAINING_SHE' => 100,
                'AUDIT_SMKP_SCORE' => 65,
            ];

            foreach ($dailyDefaults as $code => $value) {
                $indicator = Indicator::where('code', $code)->first();
                if (! $indicator) {
                    continue;
                }

                IndicatorDaily::updateOrCreate(
                    ['site_id' => $site->id, 'indicator_id' => $indicator->id, 'date' => now()->toDateString()],
                    ['value' => $value, 'note' => 'Seed Index Kinerja K3L']
                );

                IndicatorValue::updateOrCreate(
                    ['site_id' => $site->id, 'indicator_id' => $indicator->id, 'year' => now()->year, 'month' => now()->month],
                    ['value' => $value]
                );
            }
        });
    }
}
