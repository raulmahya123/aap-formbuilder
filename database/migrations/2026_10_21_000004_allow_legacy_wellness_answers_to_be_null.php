<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        // Current readiness submissions do not collect these two legacy questions.
        // Preserve historic answers and represent uncollected answers as NULL.
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->string('mood', 30)->nullable()->change();
            $table->unsignedTinyInteger('stress_level')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('wellness_responses')->whereNull('mood')->orWhereNull('stress_level')->exists()) {
            throw new RuntimeException('Cannot restore required legacy answers while current submissions contain uncollected answers. No data was changed.');
        }
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->string('mood', 30)->nullable(false)->change();
            $table->unsignedTinyInteger('stress_level')->nullable(false)->change();
        });
    }
};
