<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $t) {
            // 1) Tambah kolom company_id (nullable dulu supaya aman untuk data lama)
            $t->foreignId('company_id')
              ->nullable()
              ->after('id')
              ->constrained('companies')
              ->cascadeOnDelete();

            // 2) Siapkan kolom slug agar tidak lagi unique global
            //    (drop index unique lama: "forms_slug_unique" adalah nama default Laravel)
            $t->dropUnique('forms_slug_unique');
        });

        // --- OPSIONAL: backfill nilai company_id di sini ---
        // Contoh placeholder: set ke 1 sementara (ubah sesuai kebutuhanmu)
        // DB::table('forms')->update(['company_id' => 1]);

        Schema::table('forms', function (Blueprint $t) {
            // 3) Unique baru: slug per perusahaan
            $t->unique(['company_id', 'slug'], 'forms_company_slug_unique');

            // 4) Index bantu untuk query umum
            $t->index(['company_id', 'doc_type', 'is_active'], 'forms_company_doctype_active_idx');
        });

        // 5) (Opsional tapi disarankan) jadikan NOT NULL setelah backfill selesai
        //    Kalau belum siap backfill, lewati blok ini dulu.
        // Schema::table('forms', function (Blueprint $t) {
        //     $t->foreignId('company_id')->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        // Balikkan perubahan: hapus unique & index baru, kembalikan unique slug global, drop FK/kolom
        Schema::table('forms', function (Blueprint $t) {
            // hapus composite unique & index
            $t->dropUnique('forms_company_slug_unique');
            $t->dropIndex('forms_company_doctype_active_idx');

            // kembalikan unique slug global
            $t->unique('slug', 'forms_slug_unique');

            // drop FK & kolom
            $t->dropForeign(['company_id']);
            $t->dropColumn('company_id');
        });
    }
};
