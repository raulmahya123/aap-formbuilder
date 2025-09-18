<?php

namespace Database\Seeders;

use App\Models\Indicator;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            DemoDepartmentSeeder::class,
            SampleFormSeeder::class,
            IndicatorSeeder::class,
        ]);
    }
}
