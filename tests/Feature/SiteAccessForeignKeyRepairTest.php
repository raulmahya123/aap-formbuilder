<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteAccessForeignKeyRepairTest extends TestCase
{
    private function prepareRepair(int $orphans): object
    {
        Schema::shouldReceive('hasTable')->with('user_site_access')->once()->andReturn(true);
        Schema::shouldReceive('hasTable')->with('users')->once()->andReturn(true);
        Schema::shouldReceive('hasColumn')->with('user_site_access', 'user_id')->once()->andReturn(true);
        DB::shouldReceive('getDriverName')->once()->andReturn('mysql');
        DB::shouldReceive('select')->once()->andReturn([(object) ['CONSTRAINT_NAME'=>'user_site_access_user_id_foreign', 'REFERENCED_TABLE_NAME'=>'userss', 'REFERENCED_COLUMN_NAME'=>'id']]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['total'=>$orphans]);
        return require database_path('migrations/2026_10_22_000001_fix_user_site_access_user_foreign_key.php');
    }

    public function test_repair_replaces_broken_reference_in_one_statement(): void
    {
        $migration = $this->prepareRepair(0);
        DB::shouldReceive('statement')->once()->with('ALTER TABLE `user_site_access` DROP FOREIGN KEY `user_site_access_user_id_foreign`, ADD CONSTRAINT `user_site_access_users_fk_20261022` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE')->andReturn(true);
        $migration->up();
        $this->assertTrue(true);
    }

    public function test_orphaned_permissions_stop_repair_without_changing_schema(): void
    {
        $migration = $this->prepareRepair(1);
        DB::shouldReceive('statement')->never();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak ada data yang diubah');
        $migration->up();
    }
}
