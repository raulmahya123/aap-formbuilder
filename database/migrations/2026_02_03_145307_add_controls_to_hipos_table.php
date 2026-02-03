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
    {Schema::table('hipo_reports', function (Blueprint $table) {
    $table->string('pic_engineering');
    $table->string('pic_administrative');
    $table->string('pic_work_practice');
    $table->string('pic_ppe');

    $table->string('evidence_engineering');
    $table->string('evidence_administrative');
    $table->string('evidence_work_practice');
    $table->string('evidence_ppe');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hipos', function (Blueprint $table) {
            //
        });
    }
};
