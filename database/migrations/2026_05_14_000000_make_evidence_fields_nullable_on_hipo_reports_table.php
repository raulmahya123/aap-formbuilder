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
        Schema::table('hipo_reports', function (Blueprint $table) {
            $table->string('evidence_engineering')->nullable()->change();
            $table->string('evidence_administrative')->nullable()->change();
            $table->string('evidence_work_practice')->nullable()->change();
            $table->string('evidence_ppe')->nullable()->change();
            
            $table->string('pic_engineering')->nullable()->change();
            $table->string('pic_administrative')->nullable()->change();
            $table->string('pic_work_practice')->nullable()->change();
            $table->string('pic_ppe')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hipo_reports', function (Blueprint $table) {
            $table->string('evidence_engineering')->nullable(false)->change();
            $table->string('evidence_administrative')->nullable(false)->change();
            $table->string('evidence_work_practice')->nullable(false)->change();
            $table->string('evidence_ppe')->nullable(false)->change();
            
            $table->string('pic_engineering')->nullable(false)->change();
            $table->string('pic_administrative')->nullable(false)->change();
            $table->string('pic_work_practice')->nullable(false)->change();
            $table->string('pic_ppe')->nullable(false)->change();
        });
    }
};
