<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\{Department, User};

class DemoDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // ====== Definisi 10 departemen (slug, name, color) ======
        $departmentsData = [
            ['slug' => 'operasional', 'name' => 'Operasional',           'color' => '#e61caf'],
            ['slug' => 'hrga',        'name' => 'HRGA',                  'color' => '#ff3b30'],
            ['slug' => 'finance',     'name' => 'Finance',               'color' => '#34c759'],
            ['slug' => 'it',          'name' => 'IT',                    'color' => '#0ea5e9'],
            ['slug' => 'hse',         'name' => 'HSE',                   'color' => '#f59e0b'],
            ['slug' => 'scm',         'name' => 'SCM/Procurement',       'color' => '#8b5cf6'],
            ['slug' => 'engineering', 'name' => 'Engineering',           'color' => '#ef4444'],
            ['slug' => 'marketing',   'name' => 'Marketing',             'color' => '#14b8a6'],
            ['slug' => 'sales',       'name' => 'Sales',                 'color' => '#f97316'],
            ['slug' => 'admin',       'name' => 'Admin',                 'color' => '#64748b'],
        ];

        $hasColor = Schema::hasColumn('departments', 'color');

        $departments = [];
        foreach ($departmentsData as $d) {
            $payload = ['name' => $d['name']];
            if ($hasColor) {
                $payload['color'] = $d['color'];
            }
            $departments[$d['slug']] = Department::firstOrCreate(
                ['slug' => $d['slug']],
                $payload
            );
        }

        // ====== Users ======
        $super = User::where('email','super@aap.test')->first();

        $u1 = User::firstOrCreate(
            ['email' => 'andi@aap.test'],
            ['name' => 'Andi', 'password' => bcrypt('password'), 'role' => 'user']
        );

        $u2 = User::firstOrCreate(
            ['email' => 'sinta@aap.test'],
            ['name' => 'Sinta', 'password' => bcrypt('password'), 'role' => 'user']
        );

        // Helper untuk ambil ID cepat
        $id = fn(string $slug) => $departments[$slug]->id ?? null;

        // ====== Akses (pivot department_user_roles) ======
        // Super Admin: dept_admin di SEMUA departemen
        if ($super) {
            $attach = [];
            foreach ($departments as $dep) {
                $attach[$dep->id] = ['dept_role' => 'dept_admin'];
            }
            $super->departments()->syncWithoutDetaching($attach);
        }

        // Andi: dept_admin di Operasional + IT, member di sisanya
        $attachAndi = [
            $id('operasional') => ['dept_role' => 'dept_admin'],
            $id('it')          => ['dept_role' => 'dept_admin'],
        ];
        foreach ($departments as $dep) {
            $attachAndi[$dep->id] = $attachAndi[$dep->id] ?? ['dept_role' => 'member'];
        }
        $u1->departments()->syncWithoutDetaching($attachAndi);

        // Sinta: member di Operasional, HRGA, HSE, Finance
        $u2->departments()->syncWithoutDetaching(array_filter([
            $id('operasional') => ['dept_role' => 'member'],
            $id('hrga')        => ['dept_role' => 'member'],
            $id('hse')         => ['dept_role' => 'member'],
            $id('finance')     => ['dept_role' => 'member'],
        ]));

        // (Opsional) Tambah dua user demo lain cepat:
        $u3 = User::firstOrCreate(
            ['email' => 'budi@aap.test'],
            ['name' => 'Budi', 'password' => bcrypt('password'), 'role' => 'user']
        );
        $u3->departments()->syncWithoutDetaching([
            $id('scm')        => ['dept_role' => 'dept_admin'],
            $id('engineering')=> ['dept_role' => 'member'],
        ]);

        $u4 = User::firstOrCreate(
            ['email' => 'rina@aap.test'],
            ['name' => 'Rina', 'password' => bcrypt('password'), 'role' => 'user']
        );
        $u4->departments()->syncWithoutDetaching([
            $id('marketing')  => ['dept_role' => 'dept_admin'],
            $id('sales')      => ['dept_role' => 'member'],
            $id('admin')      => ['dept_role' => 'member'],
        ]);
    }
}
