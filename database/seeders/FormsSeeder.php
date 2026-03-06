<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormsSeeder extends Seeder
{
    public function run(): void
    {

        // SOP 014
        $title = "SHE-SOP-014 Inspeksi Terencana Area Kerja";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-014 Inspeksi Terencana Area Kerja.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 015
        $title = "SHE-SOP-015 Akuntabilitas KPL";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-015 Akuntabilitas KPL.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 016
        $title = "SHE-SOP-016 Inspeksi Tidak Terencana dan Golden Rule";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-016 Inspeksi Tidak Terencana dan Golden Rule.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 017
        $title = "SHE-SOP-017 Inspeksi Peralatan";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-017 Inspeksi Peralatan.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 018
        $title = "SHE-SOP-018 Izin Kerja Khusus";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-018 Izin Kerja Khusus.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 019
        $title = "SHE-SOP-019 Pemantauan Penyalahgunaan Pemakaian Alkohol dan Obat-obatan Terlarang";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-019 Pemantauan Penyalahgunaan Pemakaian Alkohol dan Obat-obatan Terlarang.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 020
        $title = "SHE-SOP-020 Pelaporan Investigasi Incident";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-020 Pelaporan Investigasi Incident.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 021
        $title = "SHE-SOP-021 Kesiapan Bekerja";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-021 Kesiapan Bekerja.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 022
        $title = "SHE-SOP-022 Alat Pelindung Diri (APD)";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-022 Alat Pelindung Diri (APD).pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 023
        $title = "SHE-SOP-023 Pengelolaan Kesehatan Kerja";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-023 Pengelolaan Kesehatan Kerja.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 024
        $title = "SHE-SOP-024 Pertolongan Pertama pada Kecelakaan (P3K)";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-024 Pertolongan Pertama pada Kecelakaan (P3K).pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 025
        $title = "SHE-SOP-025 Pengelolaan Keadaan Darurat";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-025 Pengelolaan Keadaan Darurat.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 026
        $title = "SHE-SOP-026 Pemantauan & Pengukuran Lingkungan Kerja dan Lingkungan Hidup";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-026 Pemantauan & Pengukuran Lingkungan Kerja dan Lingkungan Hidup.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 027
        $title = "SHE-SOP-027 Fatigue Management";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-027 Fatigue Management.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 029
        $title = "SHE-SOP-029 Pengelolaan Ergonomi";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-029 Pengelolaan Ergonomi.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 030
        $title = "SHE-SOP--030 Pengelolaan Keselamatan Operational";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP--030 Pengelolaan Keselamatan Operational.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 031
        $title = "SHE-SOP-031 Comisioning";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-031 Comisioning.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 032
        $title = "SHE-SOP-032 Penanganan Penyakit Akibat Kerja (PAK)";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-032 Penanganan Penyakit Akibat Kerja (PAK).pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 033
        $title = "SHE-SOP-033 Perancangan dan Rekayasa";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-033 Perancangan dan Rekayasa.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 034
        $title = "SHE-SOP-034 Pengelolaan Perubahan (Management of Change)";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-034 Pengelolaan Perubahan (Management of Change).pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 035
        $title = "SHE-SOP-035 Kalibrasi Peralatan";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-035 Kalibrasi Peralatan.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 036
        $title = "SHE-SOP-036 Pengendalian Dokumen";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-036 Pengendalian Dokumen.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 037
        $title = "SHE-SOP-037 Pengendalian Rekaman";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-037 Pengendalian Rekaman.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 038
        $title = "SHE-SOP-038 Audit System Terintegrasi";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-038 Audit System Terintegrasi.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 039
        $title = "SHE-SOP-039 Rencana Perbaikan dan Tindak Lanjut Ketidaksesuaian";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-039 Rencana Perbaikan dan Tindak Lanjut Ketidaksesuaian.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 040
        $title = "SHE-SOP-040 Keselamatan di luar pekerjaan";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-040 Keselamatan di luar pekerjaan.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // SOP 041
        $title = "SHE-SOP-041 Tinjauan Management";
        DB::table('forms')->updateOrInsert(
            ['company_id' => 2, 'slug' => Str::slug($title)],
            [
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/SHE-SOP-041 Tinjauan Management.pdf',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
