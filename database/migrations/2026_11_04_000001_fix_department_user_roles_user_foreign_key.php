<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('department_user_roles')
            || !Schema::hasTable('users')
            || !Schema::hasColumn('department_user_roles', 'user_id')) {
            return;
        }

        $keys = DB::select(<<<'SQL'
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'department_user_roles'
              AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL
        SQL);

        if (count($keys) === 1
            && $keys[0]->REFERENCED_TABLE_NAME === 'users'
            && $keys[0]->REFERENCED_COLUMN_NAME === 'id') {
            return;
        }

        $orphans = DB::selectOne(<<<'SQL'
            SELECT COUNT(*) AS total
            FROM department_user_roles AS roles
            LEFT JOIN users ON users.id = roles.user_id
            WHERE users.id IS NULL
        SQL);

        if ((int) $orphans->total > 0) {
            throw new RuntimeException(
                'Perbaikan relasi dihentikan: ada role departemen tanpa pengguna yang cocok di tabel users. '
                .'Rekonsiliasi identitas pengguna terlebih dahulu; tidak ada data yang diubah.'
            );
        }

        $clauses = array_map(
            fn ($key) => 'DROP FOREIGN KEY `'.str_replace('`', '``', $key->CONSTRAINT_NAME).'`',
            $keys
        );
        $clauses[] = 'ADD CONSTRAINT `department_user_roles_users_fk_20261104` '
            .'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE';

        DB::statement('ALTER TABLE `department_user_roles` '.implode(', ', $clauses));
    }

    public function down(): void
    {
        // Data-integrity repair: do not restore the invalid userss reference.
    }
};
