<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('forms') || !Schema::hasColumn('forms', 'created_by') || !Schema::hasTable('users')) {
            return;
        }

        $current = $this->createdByForeignKey();
        if ($current && $current->REFERENCED_TABLE_NAME === 'users') {
            return;
        }

        if ($current) {
            DB::statement('ALTER TABLE `forms` DROP FOREIGN KEY `'.$current->CONSTRAINT_NAME.'`');
        }

        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');
        if ($fallbackUserId) {
            DB::table('forms')
                ->whereNotIn('created_by', DB::table('users')->select('id'))
                ->update(['created_by' => $fallbackUserId]);
        }

        DB::statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $current = $this->createdByForeignKey();
        if ($current) {
            DB::statement('ALTER TABLE `forms` DROP FOREIGN KEY `'.$current->CONSTRAINT_NAME.'`');
        }
    }

    private function createdByForeignKey(): ?object
    {
        $keys = DB::select(<<<'SQL'
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'forms'
                AND COLUMN_NAME = 'created_by'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        SQL);

        return $keys[0] ?? null;
    }
};
