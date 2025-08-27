<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Buat 2 department
        $ops = \App\Models\Department::firstOrCreate(
            ['slug' => 'operasional'],
            ['name' => 'Operasional']
        );

        $hrga = \App\Models\Department::firstOrCreate(
            ['slug' => 'hrga'],
            ['name' => 'HRGA']
        );

        // Ambil super admin
        $super = \App\Models\User::where('email','super@aap.test')->first();

        // Buat 2 user demo
        $u1 = \App\Models\User::firstOrCreate(
            ['email' => 'andi@aap.test'],
            ['name' => 'Andi', 'password' => bcrypt('password'), 'role' => 'user']
        );

        $u2 = \App\Models\User::firstOrCreate(
            ['email' => 'sinta@aap.test'],
            ['name' => 'Sinta', 'password' => bcrypt('password'), 'role' => 'user']
        );

        // Tetapkan akses department_user_roles
        // super admin jadi dept_admin di Operasional
        if ($super) {
            $super->departments()->syncWithoutDetaching([
                $ops->id => ['dept_role' => 'dept_admin'],
            ]);
        }

        // Andi = dept_admin Operasional, member HRGA
        $u1->departments()->syncWithoutDetaching([
            $ops->id => ['dept_role' => 'dept_admin'],
            $hrga->id => ['dept_role' => 'member'],
        ]);

        // Sinta = member Operasional
        $u2->departments()->syncWithoutDetaching([
            $ops->id => ['dept_role' => 'member'],
        ]);
    }
}
