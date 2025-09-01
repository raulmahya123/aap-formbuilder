<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahan kolom agar tabel `documents` sinkron dengan `document_templates`
     * - Menyediakan FK ke template
     * - Menambahkan kolom JSON untuk menyimpan snapshot layout
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Relasi ke template (nullable supaya dokumen bisa berdiri sendiri)
            if (!Schema::hasColumn('documents', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('id')
                      ->comment('Relasi ke document_templates (template asal dokumen)');
                $table->foreign('template_id')->references('id')->on('document_templates')->nullOnDelete();
            }

            // Snapshot layout (size, margin, font) dari template / default
            if (!Schema::hasColumn('documents', 'layout_config')) {
                $table->json('layout_config')->nullable()->after('class')
                      ->comment('Snapshot layout dari template saat dokumen dibuat');
            }

            // Pastikan kolom JSON konfigurasi ada
            foreach (['header_config','footer_config','signature_config','sections'] as $col) {
                if (!Schema::hasColumn('documents',$col)) {
                    $table->json($col)->nullable()->comment("Konfigurasi {$col} (copy dari template atau custom)");
                }
            }
        });
    }

    /**
     * Rollback perubahan: drop kolom tambahan
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents','template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }

            if (Schema::hasColumn('documents','layout_config')) {
                $table->dropColumn('layout_config');
            }

            foreach (['header_config','footer_config','signature_config','sections'] as $col) {
                if (Schema::hasColumn('documents',$col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
