<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hipo_reports', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();

            // Identitas Laporan
            $table->string('jobsite');
            $table->string('reporter_name');
            $table->dateTime('report_time');
            $table->enum('shift', ['Shift 1', 'Shift 2']);
            $table->enum('source', [
                'Hazard Report',
                'Safety Inspection',
                'PTO'
            ]);

            // Kategori
            $table->enum('category', [
                'High Potential Hazard',
                'Nearmiss'
            ]);

            // Detail
            $table->text('description');
            $table->enum('potential_consequence', [
                'Fatality',
                'LTI',
                'Injury Non LTI',
                'Property Damage',
                'Environment Accident'
            ]);

            // Risk & Stop Work
            $table->string('risk_level')->nullable();
            $table->boolean('stop_work')->default(false);

            // Control Actions
            $table->text('control_engineering')->nullable();
            $table->text('control_administrative')->nullable();
            $table->text('control_work_practice')->nullable();
            $table->text('control_ppe')->nullable();

            // PIC & Status
            $table->string('pic')->nullable();
            $table->enum('status', ['Open', 'Closed'])->default('Open');

            // Evidence
            $table->string('evidence_file')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hipo_reports');
    }
};
