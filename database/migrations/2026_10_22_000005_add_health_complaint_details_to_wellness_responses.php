<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wellness_responses', fn (Blueprint $table) => $table->text('health_complaint_details')->nullable());
    }

    public function down(): void
    {
        Schema::table('wellness_responses', fn (Blueprint $table) => $table->dropColumn('health_complaint_details'));
    }
};
