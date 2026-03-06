<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('forms')->insert([
            'department_id' => 5,
            'created_by' => 1,
            'title' => 'SHE SOP 030 Pengelolaan Keselamatan',
            'slug' => 'she-sop-030-pengelolaan-keselamatan',
            'doc_type' => 'SOP',
            'description' => 'Dokumen SOP Pengelolaan Keselamatan',
            'type' => 'pdf',
            'schema' => null,
            'pdf_path' => 'assets/form/SHE-SOP--030-Pengelolaan-Keselamatan.pdf',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}