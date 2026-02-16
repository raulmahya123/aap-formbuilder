<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hipo_reports', function (Blueprint $table) {

            // =========================
            // TAMBAHAN BARU (CEK DULU)
            // =========================

            if (!Schema::hasColumn('hipo_reports', 'jenis_hipo')) {
                $table->enum('jenis_hipo', ['HIPO', 'Nearmiss'])
                      ->after('report_time');
            }

            if (!Schema::hasColumn('hipo_reports', 'kta')) {
                $table->text('kta')->after('risk_level');
            }

            if (!Schema::hasColumn('hipo_reports', 'tta')) {
                $table->text('tta')->after('kta');
            }

            if (!Schema::hasColumn('hipo_reports', 'pic_engineering')) {
                $table->string('pic_engineering')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'pic_administrative')) {
                $table->string('pic_administrative')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'pic_work_practice')) {
                $table->string('pic_work_practice')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'pic_ppe')) {
                $table->string('pic_ppe')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'evidence_engineering')) {
                $table->string('evidence_engineering')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'evidence_administrative')) {
                $table->string('evidence_administrative')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'evidence_work_practice')) {
                $table->string('evidence_work_practice')->nullable();
            }

            if (!Schema::hasColumn('hipo_reports', 'evidence_ppe')) {
                $table->string('evidence_ppe')->nullable();
            }
        });

        // =========================
        // UPDATE STATUS ENUM (OPSIONAL)
        // =========================

        if (Schema::hasColumn('hipo_reports', 'status')) {
            Schema::table('hipo_reports', function (Blueprint $table) {
                $table->enum('status', ['Open', 'On Progress', 'Closed', 'Rejected'])
                      ->default('Open')
                      ->change();
            });
        }

        // =========================
        // DROP KOLON LAMA (JIKA ADA)
        // =========================

        Schema::table('hipo_reports', function (Blueprint $table) {

            if (Schema::hasColumn('hipo_reports', 'pic')) {
                $table->dropColumn('pic');
            }

            if (Schema::hasColumn('hipo_reports', 'evidence_file')) {
                $table->dropColumn('evidence_file');
            }
        });
    }

    public function down(): void
    {
        // Biarkan kosong supaya tidak merusak data existing
    }
};
