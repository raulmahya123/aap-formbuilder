<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- 1) Tambah kolom company_id & site_id (nullable dulu agar aman backfill) ---
        Schema::table('forms', function (Blueprint $t) {
            // company_id setelah id
            $t->foreignId('company_id')
              ->nullable()
              ->after('id')
              ->constrained('companies')
              ->cascadeOnDelete();

            // site_id setelah company_id
            $t->foreignId('site_id')
              ->nullable()
              ->after('company_id')
              ->constrained('sites')
              ->nullOnDelete(); // kalau site dihapus → set NULL (atau ->cascadeOnDelete() kalau mau ikut terhapus)

            // drop unique lama di slug (global)
            $t->dropUnique('forms_slug_unique');
        });

        // --- 2) Unique baru untuk slug ---
        Schema::table('forms', function (Blueprint $t) {
            // A) slug unik per COMPANY (disarankan; tetap konsisten walau site null)
            $t->unique(['company_id', 'slug'], 'forms_company_slug_unique');

            // Jika kamu lebih suka slug unik per (company, site), ganti baris di atas dengan yg ini:
            // $t->unique(['company_id', 'site_id', 'slug'], 'forms_company_site_slug_unique');

            // Index bantu untuk query umum
            $t->index(['company_id', 'doc_type', 'is_active'], 'forms_company_doctype_active_idx');
            $t->index(['site_id', 'doc_type', 'is_active'], 'forms_site_doctype_active_idx');
        });

        // --- 3) OPTIONAL: setelah backfill, boleh jadikan NOT NULL ---
        // Schema::table('forms', function (Blueprint $t) {
        //     $t->foreignId('company_id')->nullable(false)->change();
        //     // kalau site wajib: $t->foreignId('site_id')->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $t) {
            // hapus index bantu
            $t->dropIndex('forms_company_doctype_active_idx');
            $t->dropIndex('forms_site_doctype_active_idx');

            // hapus unique baru (pilih sesuai yang kamu aktifkan di up())
            if (Schema::hasColumn('forms', 'company_id')) {
                // jika kamu pakai company+slug:
                $t->dropUnique('forms_company_slug_unique');
                // jika kamu pakai company+site+slug, pakai ini:
                // $t->dropUnique('forms_company_site_slug_unique');
            }

            // kembalikan unique slug global
            $t->unique('slug', 'forms_slug_unique');

            // drop FK & kolom
            if (Schema::hasColumn('forms', 'site_id')) {
                $t->dropForeign(['site_id']);
                $t->dropColumn('site_id');
            }
            if (Schema::hasColumn('forms', 'company_id')) {
                $t->dropForeign(['company_id']);
                $t->dropColumn('company_id');
            }
        });
    }
};
