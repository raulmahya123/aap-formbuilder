<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ccm_reports', function (Blueprint $table) {

            /* =================================
             * ALASAN JIKA KEGIATAN TIDAK ADA
             * ================================= */

            $table->text('kendaraan_tidak_ada_alasan')
                  ->nullable()
                  ->after('kendaraan_ada_kegiatan');

            $table->text('izin_kerja_tidak_ada_alasan')
                  ->nullable()
                  ->after('izin_kerja_ada');

            $table->text('tebing_tidak_ada_alasan')
                  ->nullable()
                  ->after('tebing_ada');

            $table->text('air_lumpur_tidak_ada_alasan')
                  ->nullable()
                  ->after('air_lumpur_ada');

            $table->text('chainsaw_tidak_ada_alasan')
                  ->nullable()
                  ->after('chainsaw_ada');

            $table->text('loto_tidak_ada_alasan')
                  ->nullable()
                  ->after('loto_ada');

            $table->text('lifting_tidak_ada_alasan')
                  ->nullable()
                  ->after('lifting_ada');

            $table->text('blasting_tidak_ada_alasan')
                  ->nullable()
                  ->after('blasting_ada');

            $table->text('kritis_baru_tidak_ada_alasan')
                  ->nullable()
                  ->after('kritis_baru_ada');
        });
    }

    public function down(): void
    {
        Schema::table('ccm_reports', function (Blueprint $table) {
            $table->dropColumn([
                'kendaraan_tidak_ada_alasan',
                'izin_kerja_tidak_ada_alasan',
                'tebing_tidak_ada_alasan',
                'air_lumpur_tidak_ada_alasan',
                'chainsaw_tidak_ada_alasan',
                'loto_tidak_ada_alasan',
                'lifting_tidak_ada_alasan',
                'blasting_tidak_ada_alasan',
                'kritis_baru_tidak_ada_alasan',
            ]);
        });
    }
};
