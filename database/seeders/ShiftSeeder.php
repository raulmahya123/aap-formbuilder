<?php

// database/seeders/ShiftSeeder.php
namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder {
    public function run(): void {
        Shift::updateOrCreate(['code' => 'S1'], [
            'name' => 'Shift 1',
            'start_time' => '06:00:00',
            'end_time'   => '10:00:00',
            'cross_midnight' => false,
            'grace_minutes'  => 0,
        ]);

        Shift::updateOrCreate(['code' => 'S2'], [
            'name' => 'Shift 2 (pagi)',
            'start_time' => '01:00:00',
            'end_time'   => '03:00:00',
            'cross_midnight' => false,      // kalau nanti ada 22:00–02:00, set true
            'grace_minutes'  => 0,
        ]);
    }
}
