<?php

namespace Database\Seeders;

use App\Models\SapInspectionItem;
use App\Models\SapInspectionSection;
use App\Models\SapInspectionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SapInspectionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $templates = [
                ['code' => 'SHE-FM-005', 'name' => 'Formulir Inspeksi K3 Umum', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi umum aspek K3 di area kerja'],
                ['code' => 'SHE-FM-009', 'name' => 'Formulir Inspeksi APD', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi kelengkapan dan penggunaan APD'],
                ['code' => 'SHE-FM-014', 'name' => 'Formulir Inspeksi Peralatan Kerja', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi peralatan, alat tangan dan mesin'],
                ['code' => 'SHE-FM-016', 'name' => 'Formulir Inspeksi Tempat Kerja', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi kondisi tempat kerja, housekeeping, akses'],
                ['code' => 'SHE-FM-017', 'name' => 'Formulir Inspeksi Listrik & Instalasi', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi instalasi listrik, grounding, panel'],
                ['code' => 'SHE-FM-018', 'name' => 'Formulir Inspeksi Alat Angkat & Angkut', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi crane, forklift, rigging'],
                ['code' => 'SHE-FM-021', 'name' => 'Formulir Inspeksi Konstruksi', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi pekerjaan konstruksi & pekerjaan berisiko'],
                ['code' => 'SHE-FM-032', 'name' => 'Formulir Inspeksi Lingkungan', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi aspek lingkungan, limbah, kebersihan'],
                ['code' => 'SHE-FM-055', 'name' => 'Formulir Inspeksi Kebakaran & APAR', 'scoring_type' => 'numeric_1_4', 'description' => 'Inspeksi proteksi kebakaran, APAR, hydrant, jalur evakuasi'],
            ];

            foreach ($templates as $tdata) {
                $template = SapInspectionTemplate::firstOrCreate(
                    ['code' => $tdata['code']],
                    [
                        'name' => $tdata['name'],
                        'description' => $tdata['description'],
                        'scoring_type' => $tdata['scoring_type'],
                        'is_active' => true,
                        'version' => 1,
                    ]
                );

                // Create default sections/items if none exist
                if ($template->sections()->count() === 0) {
                    $sections = [
                        ['code' => 'GEN', 'title' => 'Aspek Umum', 'sort_order' => 1],
                        ['code' => 'ADM', 'title' => 'Administrasi & Dokumentasi', 'sort_order' => 2],
                        ['code' => 'COND', 'title' => 'Kondisi Fisik & Keselamatan', 'sort_order' => 3],
                        ['code' => 'ACT', 'title' => 'Tindakan & Kepatuhan', 'sort_order' => 4],
                    ];

                    foreach ($sections as $sdata) {
                        $section = SapInspectionSection::create([
                            'template_id' => $template->id,
                            'code' => $sdata['code'],
                            'title' => $sdata['title'],
                            'sort_order' => $sdata['sort_order'],
                        ]);

                        $items = [
                            ['question' => 'Apakah area kerja dalam kondisi aman dan tertib?', 'answer_type' => 'score_1_4', 'sort_order' => 1],
                            ['question' => 'Apakah pekerja menggunakan APD sesuai kebutuhan pekerjaan?', 'answer_type' => 'score_1_4', 'sort_order' => 2],
                            ['question' => 'Apakah tersedia dan berfungsi peralatan proteksi kebakaran?', 'answer_type' => 'score_1_4', 'sort_order' => 3],
                            ['question' => 'Apakah akses jalan/evakuasi bebas hambatan?', 'answer_type' => 'score_1_4', 'sort_order' => 4],
                            ['question' => 'Apakah housekeeping/5R diterapkan dengan baik?', 'answer_type' => 'score_1_4', 'sort_order' => 5],
                            ['question' => 'Apakah tersedia tanda peringatan/safety sign yang sesuai?', 'answer_type' => 'score_1_4', 'sort_order' => 6],
                            ['question' => 'Apakah ada temuan bahaya potensial yang perlu ditindaklanjuti?', 'answer_type' => 'yes_no_na', 'sort_order' => 7],
                            ['question' => 'Catatan/observasi tambahan', 'answer_type' => 'text', 'sort_order' => 8],
                        ];

                        foreach ($items as $idata) {
                            SapInspectionItem::create([
                                'section_id' => $section->id,
                                'template_id' => $template->id,
                                'question' => $idata['question'],
                                'answer_type' => $idata['answer_type'],
                                'sort_order' => $idata['sort_order'],
                                'is_critical' => in_array($idata['sort_order'], [2,3,4]) ? true : false,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
