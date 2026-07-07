<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hipo_reports') || !Schema::hasColumn('hipo_reports', 'user_id') || !Schema::hasTable('users')) {
            return;
        }

        $current = $this->userIdForeignKey();
        if ($current && $current->REFERENCED_TABLE_NAME === 'users') {
            return;
        }

        if ($current) {
            DB::statement('ALTER TABLE `hipo_reports` DROP FOREIGN KEY `'.$current->CONSTRAINT_NAME.'`');
        }

        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');
        if ($fallbackUserId) {
            DB::table('hipo_reports')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->update(['user_id' => $fallbackUserId]);
        }

        DB::statement('ALTER TABLE `hipo_reports` ADD CONSTRAINT `hipo_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $current = $this->userIdForeignKey();
        if ($current) {
            DB::statement('ALTER TABLE `hipo_reports` DROP FOREIGN KEY `'.$current->CONSTRAINT_NAME.'`');
        }
    }

    private function userIdForeignKey(): ?object
    {
        $keys = DB::select(<<<'SQL'
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'hipo_reports'
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        SQL);

        return $keys[0] ?? null;
    }
};
