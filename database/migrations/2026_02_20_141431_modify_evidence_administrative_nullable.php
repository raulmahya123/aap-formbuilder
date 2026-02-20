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
        $table->string('evidence_administrative')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('hipo_reports', function (Blueprint $table) {
        $table->string('evidence_administrative')->nullable(false)->change();
    });
}
};
