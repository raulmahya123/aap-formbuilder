<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DashboardDemoSeeder::class,
            SuperAdminSeeder::class,
            DemoDepartmentSeeder::class,
            SiteSeeder::class,
            IndicatorSeeder::class,
            ReportChartDemoSeeder::class,
            ShiftSeeder::class,
            FormsSeeder::class,
            SampleFormSeeder::class,
        ]);
    }
}
