<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormsSeeder extends Seeder
{
    public function run(): void
    {

        $files = [

            "SHE-SOP-014 Inspeksi Terencana Area Kerja.pdf",
            "SHE-SOP-015 Akuntabilitas KPL.pdf",
            "SHE-SOP-016 Inspeksi Tidak Terencana dan Golden Rule.pdf",
            "SHE-SOP-017 Inspeksi Peralatan.pdf",
            "SHE-SOP-018 Izin Kerja Khusus.pdf",
            "SHE-SOP-019 Pemantauan Penyalahgunaan Pemakaian Alkohol dan Obat-obatan Terlarang.pdf",
            "SHE-SOP-020 Pelaporan Investigasi Incident.pdf",
            "SHE-SOP-021 Kesiapan Bekerja.pdf",
            "SHE-SOP-022 Alat Pelindung Diri (APD).pdf",
            "SHE-SOP-023 Pengelolaan Kesehatan Kerja.pdf",
            "SHE-SOP-024 Pertolongan Pertama pada Kecelakaan (P3K).pdf",
            "SHE-SOP-025 Pengelolaan Keadaan Darurat.pdf",
            "SHE-SOP-026 Pemantauan & Pengukuran Lingkungan Kerja dan Lingkungan Hidup.pdf",
            "SHE-SOP-027 Fatigue Management.pdf",

            "SHE-SOP-029 Pengelolaan Ergonomi.pdf",
            "SHE-SOP-031 Comisioning.pdf",
            "SHE-SOP-032 Penanganan Penyakit Akibat Kerja (PAK).pdf",
            "SHE-SOP-033 Perancangan dan Rekayasa.pdf",
            "SHE-SOP-034 Pengelolaan Perubahan ( Management of Change).pdf",
            "SHE-SOP-035 Kalibrasi Peralatan.pdf",
            "SHE-SOP-036 Pengendalian Dokumen.pdf",
            "SHE-SOP-037 Pengendalian Rekaman.pdf",
            "SHE-SOP-038 Audit System Terintegrasi.pdf",
            "SHE-SOP-039 Rencana Perbaikan dan Tindak Lanjut Ketidaksesuaian.pdf",
            "SHE-SOP-040 Keselamatan di luar pekerjaan.pdf",
            "SHE-SOP-041 Tinjauan Management.pdf",
        ];

        foreach ($files as $file) {

            $title = str_replace('.pdf','',$file);

            DB::table('forms')->insert([
                'company_id' => 2,
                'site_id' => 5,
                'department_id' => 5,
                'created_by' => 1,
                'title' => $title,
                'slug' => Str::slug($title),
                'doc_type' => 'SOP',
                'description' => $title,
                'type' => 'pdf',
                'schema' => null,
                'pdf_path' => 'assets/form/'.$file,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}