<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ccm_reports', function (Blueprint $table) {
            $table->id();

            /* =====================
         * SECTION 1 - UMUM
         * ===================== */
            $table->date('waktu_pelaporan');
            $table->string('jobsite');
            $table->string('nama_pelapor');

            /* =====================
         * SECTION 2–4
         * Pengoperasian Kendaraan & Alat Berat
         * ===================== */
            $table->boolean('kendaraan_ada_kegiatan')->nullable();
            $table->text('kendaraan_pekerjaan_kritis')->nullable();
            $table->enum('kendaraan_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('kendaraan_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('kendaraan_engineering')->nullable();
            $table->text('kendaraan_engineering_evidence')->nullable();
            $table->text('kendaraan_administratif')->nullable();
            $table->text('kendaraan_administratif_evidence')->nullable();
            $table->text('kendaraan_praktek_kerja')->nullable();
            $table->text('kendaraan_praktek_kerja_evidence')->nullable();
            $table->text('kendaraan_apd')->nullable();
            $table->text('kendaraan_apd_evidence')->nullable();

            /* =====================
         * SECTION 5–7
         * Izin Kerja
         * ===================== */
            $table->boolean('izin_kerja_ada')->nullable();
            $table->text('izin_kerja_pekerjaan_kritis')->nullable();
            $table->enum('izin_kerja_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('izin_kerja_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('izin_engineering')->nullable();
            $table->text('izin_engineering_evidence')->nullable();
            $table->text('izin_administratif')->nullable();
            $table->text('izin_administratif_evidence')->nullable();
            $table->text('izin_praktek_kerja')->nullable();
            $table->text('izin_praktek_kerja_evidence')->nullable();
            $table->text('izin_apd')->nullable();
            $table->text('izin_apd_evidence')->nullable();

            /* =====================
         * SECTION 8–10
         * Tebing / Galian / Disposal
         * ===================== */
            $table->boolean('tebing_ada')->nullable();
            $table->text('tebing_pekerjaan_kritis')->nullable();
            $table->enum('tebing_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('tebing_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('tebing_engineering')->nullable();
            $table->text('tebing_engineering_evidence')->nullable();
            $table->text('tebing_administratif')->nullable();
            $table->text('tebing_administratif_evidence')->nullable();
            $table->text('tebing_praktek_kerja')->nullable();
            $table->text('tebing_praktek_kerja_evidence')->nullable();
            $table->text('tebing_apd')->nullable();
            $table->text('tebing_apd_evidence')->nullable();

            /* =====================
         * SECTION 11–13
         * Air & Lumpur
         * ===================== */
            $table->boolean('air_lumpur_ada')->nullable();
            $table->text('air_lumpur_pekerjaan_kritis')->nullable();
            $table->enum('air_lumpur_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('air_lumpur_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('air_lumpur_engineering')->nullable();
            $table->text('air_lumpur_engineering_evidence')->nullable();
            $table->text('air_lumpur_administratif')->nullable();
            $table->text('air_lumpur_administratif_evidence')->nullable();
            $table->text('air_lumpur_apd')->nullable();
            $table->text('air_lumpur_apd_evidence')->nullable();

            /* =====================
         * SECTION 14–16
         * Chainsaw & Land Clearing
         * ===================== */
            $table->boolean('chainsaw_ada')->nullable();
            $table->text('chainsaw_pekerjaan_kritis')->nullable();
            $table->enum('chainsaw_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('chainsaw_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('chainsaw_engineering')->nullable();
            $table->text('chainsaw_engineering_evidence')->nullable();
            $table->text('chainsaw_administratif')->nullable();
            $table->text('chainsaw_administratif_evidence')->nullable();
            $table->text('chainsaw_praktek_kerja')->nullable();
            $table->text('chainsaw_praktek_kerja_evidence')->nullable();
            $table->text('chainsaw_apd')->nullable();
            $table->text('chainsaw_apd_evidence')->nullable();

            /* =====================
         * SECTION 17–19
         * LOTO & Penanganan Ban
         * ===================== */
            $table->boolean('loto_ada')->nullable();
            $table->text('loto_pekerjaan_kritis')->nullable();
            $table->enum('loto_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('loto_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('loto_engineering')->nullable();
            $table->text('loto_engineering_evidence')->nullable();
            $table->text('loto_administratif')->nullable();
            $table->text('loto_administratif_evidence')->nullable();
            $table->text('loto_praktek_kerja')->nullable();
            $table->text('loto_praktek_kerja_evidence')->nullable();
            $table->text('loto_apd')->nullable();
            $table->text('loto_apd_evidence')->nullable();

            /* =====================
         * SECTION 20–22
         * Pengangkatan & Penyangga
         * ===================== */
            $table->boolean('lifting_ada')->nullable();
            $table->text('lifting_pekerjaan_kritis')->nullable();
            $table->enum('lifting_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('lifting_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('lifting_engineering')->nullable();
            $table->text('lifting_engineering_evidence')->nullable();
            $table->text('lifting_administratif')->nullable();
            $table->text('lifting_administratif_evidence')->nullable();
            $table->text('lifting_apd')->nullable();
            $table->text('lifting_apd_evidence')->nullable();

            /* =====================
         * SECTION 23–25
         * Blasting
         * ===================== */
            $table->boolean('blasting_ada')->nullable();
            $table->text('blasting_pekerjaan_kritis')->nullable();
            $table->enum('blasting_prosedur', ['Sudah', 'Belum'])->nullable();
            $table->enum('blasting_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('blasting_engineering')->nullable();
            $table->text('blasting_engineering_evidence')->nullable();
            $table->text('blasting_administratif')->nullable();
            $table->text('blasting_administratif_evidence')->nullable();
            $table->text('blasting_praktek_kerja')->nullable();
            $table->text('blasting_praktek_kerja_evidence')->nullable();
            $table->text('blasting_apd')->nullable();
            $table->text('blasting_apd_evidence')->nullable();

            /* =====================
         * SECTION 26–28
         * Pekerjaan Kritis Baru
         * ===================== */
            $table->boolean('kritis_baru_ada')->nullable();
            $table->text('kritis_baru_pekerjaan')->nullable();
            $table->enum('kritis_baru_prosedur', ['Ada', 'Tidak Ada'])->nullable();
            $table->enum('kritis_baru_dipahami', ['Sudah', 'Belum'])->nullable();
            $table->enum('kritis_baru_pelanggaran', ['Ada', 'Tidak Ada'])->nullable();

            $table->text('kritis_baru_engineering')->nullable();
            $table->text('kritis_baru_engineering_evidence')->nullable();
            $table->text('kritis_baru_administratif')->nullable();
            $table->text('kritis_baru_administratif_evidence')->nullable();
            $table->text('kritis_baru_praktek_kerja')->nullable();
            $table->text('kritis_baru_praktek_kerja_evidence')->nullable();
            $table->text('kritis_baru_apd')->nullable();
            $table->text('kritis_baru_apd_evidence')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ccm_reports');
    }
};
