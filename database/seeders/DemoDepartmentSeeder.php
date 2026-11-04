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
            ['slug' => 'management',             'name' => 'Management',             'color' => '#0ea5e9'],
            ['slug' => 'operation',              'name' => 'Operation',              'color' => '#e61caf'],
            ['slug' => 'engineering',            'name' => 'Engineering',            'color' => '#bb9974'],
            ['slug' => 'plant',                  'name' => 'Plant',                  'color' => '#f97316'],
            ['slug' => 'budget-and-performance', 'name' => 'Budget and Performance', 'color' => '#14b8a6'],
            ['slug' => 'asset-and-insurance',    'name' => 'Asset and Insurance',    'color' => '#64748b'],
            ['slug' => 'she',                    'name' => 'SHE',                    'color' => '#f59e0b'],
            ['slug' => 'hrga',                   'name' => 'HRGA',                   'color' => '#bb9974'],
            ['slug' => 'scm',                    'name' => 'SCM',                    'color' => '#8b5cf6'],
            ['slug' => 'finance',                'name' => 'Finance',                'color' => '#34c759'],
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

        // Andi: dept_admin di Operation + Management, member di sisanya
        $attachAndi = [
            $id('operation')  => ['dept_role' => 'dept_admin'],
            $id('management') => ['dept_role' => 'dept_admin'],
        ];
        foreach ($departments as $dep) {
            $attachAndi[$dep->id] = $attachAndi[$dep->id] ?? ['dept_role' => 'member'];
        }
        $u1->departments()->syncWithoutDetaching($attachAndi);

        // Sinta: member di Operation, HRGA, SHE, Finance
        $u2->departments()->syncWithoutDetaching(array_filter([
            $id('operation')   => ['dept_role' => 'member'],
            $id('hrga')        => ['dept_role' => 'member'],
            $id('she')         => ['dept_role' => 'member'],
            $id('finance')     => ['dept_role' => 'member'],
        ]));

        // (Opsional) Tambah dua user demo lain cepat:
        $u3 = User::firstOrCreate(
            ['email' => 'budi@aap.test'],
            ['name' => 'Budi', 'password' => bcrypt('password'), 'role' => 'user']
        );
        $u3->departments()->syncWithoutDetaching([
            $id('scm')         => ['dept_role' => 'dept_admin'],
            $id('engineering') => ['dept_role' => 'member'],
        ]);

        $u4 = User::firstOrCreate(
            ['email' => 'rina@aap.test'],
            ['name' => 'Rina', 'password' => bcrypt('password'), 'role' => 'user']
        );
        $u4->departments()->syncWithoutDetaching([
            $id('plant')                  => ['dept_role' => 'dept_admin'],
            $id('budget-and-performance') => ['dept_role' => 'member'],
            $id('asset-and-insurance')    => ['dept_role' => 'member'],
        ]);
    }
}
