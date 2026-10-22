<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') return;
        if (!Schema::hasTable('user_site_access') || !Schema::hasTable('users') || !Schema::hasColumn('user_site_access', 'user_id')) return;

        $keys = DB::select(<<<'SQL'
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_site_access'
              AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL
        SQL);
        if (count($keys) === 1 && $keys[0]->REFERENCED_TABLE_NAME === 'users' && $keys[0]->REFERENCED_COLUMN_NAME === 'id') return;

        // Never delete orphaned assignments or transfer their permissions to another user.
        $orphans = DB::selectOne(<<<'SQL'
            SELECT COUNT(*) AS total FROM user_site_access AS access_row
            LEFT JOIN users ON users.id = access_row.user_id
            WHERE users.id IS NULL
        SQL);
        if ((int) $orphans->total > 0) {
            throw new RuntimeException('Perbaikan relasi dihentikan: ada penugasan site tanpa pengguna yang cocok di tabel users. Rekonsiliasi identitas pengguna terlebih dahulu; tidak ada data yang diubah.');
        }

        $clauses = array_map(fn ($key) => 'DROP FOREIGN KEY `'.str_replace('`', '``', $key->CONSTRAINT_NAME).'`', $keys);
        $clauses[] = 'ADD CONSTRAINT `user_site_access_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE';
        // One ALTER avoids leaving the table without its foreign key if adding the new key fails.
        DB::statement('ALTER TABLE `user_site_access` '.implode(', ', $clauses));
    }

    public function down(): void
    {
        // Data-integrity repair: do not restore the invalid userss reference on rollback.
    }
};
