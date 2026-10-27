<?php

namespace Database\Seeders;

use App\Models\SapPtoChecklistItem;
use App\Models\SapPtoChecklistTemplate;
use Illuminate\Database\Seeder;

class SapPtoTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = SapPtoChecklistTemplate::updateOrCreate(
            ['code' => 'SHE-FM-019'],
            ['name' => 'Planned Task Observation (PTO) SHE-FM-019', 'is_active' => true, 'version' => 1]
        );

        $items = [
            // A
            ['section' => 'A. Prosedur / Standar / IK / JSA', 'item_no' => 1, 'question' => 'Tersedia Prosedur / Standar / Instruksi Kerja / JSA.', 'is_risk_indicator' => false, 'sort_order' => 10],
            ['section' => 'A. Prosedur / Standar / IK / JSA', 'item_no' => 2, 'question' => 'Prosedur / Standar / Instruksi Kerja / JSA dimengerti.', 'is_risk_indicator' => false, 'sort_order' => 20],
            ['section' => 'A. Prosedur / Standar / IK / JSA', 'item_no' => 3, 'question' => 'Prosedur / Standar / Instruksi Kerja / JSA dilaksanakan.', 'is_risk_indicator' => false, 'sort_order' => 30],
            // B
            ['section' => 'B. Posisi Karyawan', 'item_no' => 4, 'question' => 'APD yang sesuai dengan aktivitas digunakan dengan benar.', 'is_risk_indicator' => false, 'has_apd_field' => true, 'sort_order' => 40],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 5, 'question' => 'Potensi terpeleset.', 'is_risk_indicator' => true, 'sort_order' => 50],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 6, 'question' => 'Potensi terkait di dalam, pada, atau di antara dua benda.', 'is_risk_indicator' => true, 'sort_order' => 60],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 7, 'question' => 'Potensi terpukul atau terjepit.', 'is_risk_indicator' => true, 'sort_order' => 70],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 8, 'question' => 'Potensi tersandung, terbentur, atau tertabrak.', 'is_risk_indicator' => true, 'sort_order' => 80],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 9, 'question' => 'Potensi menyentuh bagian panas/dingin.', 'is_risk_indicator' => true, 'sort_order' => 90],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 10, 'question' => 'Potensi menyentuh arus listrik.', 'is_risk_indicator' => true, 'sort_order' => 100],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 11, 'question' => 'Potensi menghirup, menyerap, atau menelan bahan berbahaya.', 'is_risk_indicator' => true, 'sort_order' => 110],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 12, 'question' => 'Potensi terlalu memforsir tenaga.', 'is_risk_indicator' => true, 'sort_order' => 120],
            ['section' => 'B. Posisi Karyawan', 'item_no' => 13, 'question' => 'Potensi posisi berbahaya lainnya.', 'is_risk_indicator' => true, 'has_other_field' => true, 'sort_order' => 130],
            // C
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 14, 'question' => 'Apakah karyawan membetulkan posisi APD?', 'is_risk_indicator' => true, 'sort_order' => 140],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 15, 'question' => 'Apakah pekerja mengubah posisi secara tiba-tiba?', 'is_risk_indicator' => true, 'sort_order' => 150],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 16, 'question' => 'Apakah karyawan melakukan tindakan terburu-buru?', 'is_risk_indicator' => true, 'sort_order' => 160],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 17, 'question' => 'Apakah karyawan memaksakan diri dalam bekerja?', 'is_risk_indicator' => true, 'sort_order' => 170],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 18, 'question' => 'Apakah karyawan memperhatikan lingkungan kerja?', 'is_risk_indicator' => false, 'sort_order' => 180],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 19, 'question' => 'Apakah pekerja mengatur kembali pekerjaan?', 'is_risk_indicator' => false, 'sort_order' => 190],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 20, 'question' => 'Apakah pekerja menerapkan isolasi?', 'is_risk_indicator' => false, 'sort_order' => 200],
            ['section' => 'C. Tindakan / Reaksi Karyawan', 'item_no' => 21, 'question' => 'Apakah pekerja mengganti perkakas?', 'is_risk_indicator' => false, 'sort_order' => 210],
            // D
            ['section' => 'D. Alat dan Peralatan', 'item_no' => 22, 'question' => 'Peralatan tersedia dalam kondisi baik dan siap pakai.', 'is_risk_indicator' => false, 'sort_order' => 220],
            ['section' => 'D. Alat dan Peralatan', 'item_no' => 23, 'question' => 'Alat dan peralatan sesuai dengan jenis pekerjaan.', 'is_risk_indicator' => false, 'sort_order' => 230],
            ['section' => 'D. Alat dan Peralatan', 'item_no' => 24, 'question' => 'Alat dan peralatan digunakan sesuai dengan fungsinya.', 'is_risk_indicator' => false, 'sort_order' => 240],
            ['section' => 'D. Alat dan Peralatan', 'item_no' => 25, 'question' => 'Alat dan peralatan dalam kondisi aman.', 'is_risk_indicator' => false, 'sort_order' => 250],
            // E
            ['section' => 'E. Kondisi Area Kerja', 'item_no' => 26, 'question' => 'Area kerja aman dan selamat.', 'is_risk_indicator' => false, 'sort_order' => 260],
            ['section' => 'E. Kondisi Area Kerja', 'item_no' => 27, 'question' => 'Area kerja terdapat potensi bahaya lain, seperti licin, becek, atau berlumpur.', 'is_risk_indicator' => true, 'sort_order' => 270],
            ['section' => 'E. Kondisi Area Kerja', 'item_no' => 28, 'question' => 'Penempatan barang dan housekeeping tidak terjaga dengan baik.', 'is_risk_indicator' => true, 'sort_order' => 280],
            ['section' => 'E. Kondisi Area Kerja', 'item_no' => 29, 'question' => 'Item observasi tambahan sesuai isian pada dokumen asli.', 'is_risk_indicator' => true, 'has_other_field' => true, 'sort_order' => 290],
        ];

        foreach ($items as $it) {
            SapPtoChecklistItem::updateOrCreate(
                ['template_id' => $template->id, 'item_no' => $it['item_no']],
                array_merge($it, ['template_id' => $template->id])
            );
        }
    }
}
