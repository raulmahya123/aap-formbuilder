<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('qa_threads', function (Blueprint $table) {
            $table->foreignId('site_id')
                ->nullable()
                ->after('department_id')
                ->constrained('sites')
                ->nullOnDelete();
            $table->index(['site_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::table('qa_threads', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropIndex(['site_id', 'last_message_at']);
            $table->dropColumn('site_id');
        });
    }
};
