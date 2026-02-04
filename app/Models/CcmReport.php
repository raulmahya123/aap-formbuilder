<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CcmReport extends Model
{
    use HasFactory;

    protected $table = 'ccm_reports';

    protected $fillable = [

        /* =====================
         * SECTION 1 - UMUM
         * ===================== */
        'waktu_pelaporan',
        'jobsite',
        'nama_pelapor',

        /* =====================
         * Kendaraan & Alat Berat
         * ===================== */
        'kendaraan_ada_kegiatan',
        'kendaraan_tidak_ada_alasan',
        'kendaraan_pekerjaan_kritis',
        'kendaraan_prosedur',
        'kendaraan_pelanggaran',
        'kendaraan_engineering',
        'kendaraan_engineering_evidence',
        'kendaraan_administratif',
        'kendaraan_administratif_evidence',
        'kendaraan_praktek_kerja',
        'kendaraan_praktek_kerja_evidence',
        'kendaraan_apd',
        'kendaraan_apd_evidence',

        /* =====================
         * Izin Kerja
         * ===================== */
        'izin_kerja_ada',
        'izin_kerja_tidak_ada_alasan',
        'izin_kerja_pekerjaan_kritis',
        'izin_kerja_prosedur',
        'izin_kerja_pelanggaran',
        'izin_engineering',
        'izin_engineering_evidence',
        'izin_administratif',
        'izin_administratif_evidence',
        'izin_praktek_kerja',
        'izin_praktek_kerja_evidence',
        'izin_apd',
        'izin_apd_evidence',

        /* =====================
         * Tebing / Disposal
         * ===================== */
        'tebing_ada',
        'tebing_tidak_ada_alasan',
        'tebing_pekerjaan_kritis',
        'tebing_prosedur',
        'tebing_pelanggaran',
        'tebing_engineering',
        'tebing_engineering_evidence',
        'tebing_administratif',
        'tebing_administratif_evidence',
        'tebing_praktek_kerja',
        'tebing_praktek_kerja_evidence',
        'tebing_apd',
        'tebing_apd_evidence',

        /* =====================
         * Air & Lumpur
         * ===================== */
        'air_lumpur_ada',
        'air_lumpur_tidak_ada_alasan',
        'air_lumpur_pekerjaan_kritis',
        'air_lumpur_prosedur',
        'air_lumpur_pelanggaran',
        'air_lumpur_engineering',
        'air_lumpur_engineering_evidence',
        'air_lumpur_administratif',
        'air_lumpur_administratif_evidence',
        'air_lumpur_apd',
        'air_lumpur_apd_evidence',

        /* =====================
         * Chainsaw
         * ===================== */
        'chainsaw_ada',
        'chainsaw_tidak_ada_alasan',
        'chainsaw_pekerjaan_kritis',
        'chainsaw_prosedur',
        'chainsaw_pelanggaran',
        'chainsaw_engineering',
        'chainsaw_engineering_evidence',
        'chainsaw_administratif',
        'chainsaw_administratif_evidence',
        'chainsaw_praktek_kerja',
        'chainsaw_praktek_kerja_evidence',
        'chainsaw_apd',
        'chainsaw_apd_evidence',

        /* =====================
         * LOTO
         * ===================== */
        'loto_ada',
        'loto_tidak_ada_alasan',
        'loto_pekerjaan_kritis',
        'loto_prosedur',
        'loto_pelanggaran',
        'loto_engineering',
        'loto_engineering_evidence',
        'loto_administratif',
        'loto_administratif_evidence',
        'loto_praktek_kerja',
        'loto_praktek_kerja_evidence',
        'loto_apd',
        'loto_apd_evidence',

        /* =====================
         * Lifting
         * ===================== */
        'lifting_ada',
        'lifting_tidak_ada_alasan',
        'lifting_pekerjaan_kritis',
        'lifting_prosedur',
        'lifting_pelanggaran',
        'lifting_engineering',
        'lifting_engineering_evidence',
        'lifting_administratif',
        'lifting_administratif_evidence',
        'lifting_apd',
        'lifting_apd_evidence',

        /* =====================
         * Blasting
         * ===================== */
        'blasting_ada',
        'blasting_tidak_ada_alasan',
        'blasting_pekerjaan_kritis',
        'blasting_prosedur',
        'blasting_pelanggaran',
        'blasting_engineering',
        'blasting_engineering_evidence',
        'blasting_administratif',
        'blasting_administratif_evidence',
        'blasting_praktek_kerja',
        'blasting_praktek_kerja_evidence',
        'blasting_apd',
        'blasting_apd_evidence',

        /* =====================
         * Kritis Baru
         * ===================== */
        'kritis_baru_ada',
        'kritis_baru_tidak_ada_alasan',
        'kritis_baru_pekerjaan',
        'kritis_baru_prosedur',
        'kritis_baru_dipahami',
        'kritis_baru_pelanggaran',
        'kritis_baru_engineering',
        'kritis_baru_engineering_evidence',
        'kritis_baru_administratif',
        'kritis_baru_administratif_evidence',
        'kritis_baru_praktek_kerja',
        'kritis_baru_praktek_kerja_evidence',
        'kritis_baru_apd',
        'kritis_baru_apd_evidence',
    ];

    protected $casts = [
        'waktu_pelaporan' => 'date',

        'kendaraan_ada_kegiatan' => 'boolean',
        'izin_kerja_ada'         => 'boolean',
        'tebing_ada'             => 'boolean',
        'air_lumpur_ada'         => 'boolean',
        'chainsaw_ada'           => 'boolean',
        'loto_ada'               => 'boolean',
        'lifting_ada'            => 'boolean',
        'blasting_ada'           => 'boolean',
        'kritis_baru_ada'        => 'boolean',
    ];

    /* =========================================================
     * HELPER (OPSIONAL, TAPI SANGAT BERGUNA)
     * ========================================================= */

    public function hasEvidence(string $field): bool
    {
        return !empty($this->{$field . '_evidence'});
    }

    public function evidenceUrl(string $field): ?string
    {
        return $this->{$field . '_evidence'}
            ? asset('storage/' . $this->{$field . '_evidence'})
            : null;
    }
}
