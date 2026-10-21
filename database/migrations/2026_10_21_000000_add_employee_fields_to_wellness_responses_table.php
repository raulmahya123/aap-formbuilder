<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->string('nik', 50)->nullable()->after('name');
            $table->string('position', 120)->nullable()->after('nik');
            $table->string('site', 120)->nullable()->after('position');
            $table->string('shift', 20)->nullable()->after('site');
            $table->string('sleep_before_shift', 30)->nullable()->after('shift');
            $table->string('health_complaint', 40)->nullable()->after('sleep_before_shift');
            $table->string('drowsy_medication', 10)->nullable()->after('health_complaint');
            $table->string('work_readiness', 20)->nullable()->after('drowsy_medication');

            $table->index('nik');
            $table->index('site');
            $table->index('work_readiness');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->dropIndex('wellness_responses_nik_index');
            $table->dropIndex('wellness_responses_site_index');
            $table->dropIndex('wellness_responses_work_readiness_index');
            $table->dropColumn([
                'nik', 'position', 'site', 'shift', 'sleep_before_shift',
                'health_complaint', 'drowsy_medication', 'work_readiness',
            ]);
        });
    }
};
