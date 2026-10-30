<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('hipo_reports')) return;

        Schema::table('hipo_reports', function (Blueprint $table): void {
            if (!Schema::hasColumn('hipo_reports', 'assigned_admin_id')) $table->unsignedBigInteger('assigned_admin_id')->nullable();
            if (!Schema::hasColumn('hipo_reports', 'closed_by')) $table->unsignedBigInteger('closed_by')->nullable();
            if (!Schema::hasColumn('hipo_reports', 'closed_at')) $table->dateTime('closed_at')->nullable();
            if (!Schema::hasColumn('hipo_reports', 'admin_note')) $table->text('admin_note')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hipo_reports')) return;

        Schema::table('hipo_reports', function (Blueprint $table): void {
            if (Schema::hasColumn('hipo_reports', 'assigned_admin_id')) $table->dropColumn('assigned_admin_id');
            if (Schema::hasColumn('hipo_reports', 'closed_by')) $table->dropColumn('closed_by');
            if (Schema::hasColumn('hipo_reports', 'closed_at')) $table->dropColumn('closed_at');
            if (Schema::hasColumn('hipo_reports', 'admin_note')) $table->dropColumn('admin_note');
        });
    }
};
