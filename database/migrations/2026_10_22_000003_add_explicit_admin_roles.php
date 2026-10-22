<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('role', 32)->default('user')->change());
        DB::table('users')->where('role', 'admin')->update(['role'=>'admin_site']);
        // Preserve existing operational administrators while separating department membership from account role.
        DB::table('users')->where('role', 'user')->whereIn('id', DB::table('department_user_roles')->where('dept_role', 'dept_admin')->select('user_id'))->update(['role'=>'admin_site']);
    }

    public function down(): void
    {
        // Keep the expanded role column and assigned roles; rollback must not silently demote accounts.
    }
};
