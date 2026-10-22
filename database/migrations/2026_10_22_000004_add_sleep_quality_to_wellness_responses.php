<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->string('sleep_quality')->nullable();
            $table->text('sleep_quality_reason')->nullable();
            $table->string('heavy_drowsiness')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wellness_responses', fn (Blueprint $table) => $table->dropColumn(['sleep_quality', 'sleep_quality_reason', 'heavy_drowsiness']));
    }
};
