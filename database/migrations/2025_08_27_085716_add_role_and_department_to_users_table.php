<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Tambah kolom role (super_admin|user)
            if (!Schema::hasColumn('users', 'role')) {
                $t->enum('role', ['super_admin','user'])
                  ->default('user')
                  ->after('remember_token');
            }

            // (Opsional) relasi ke department; boleh kosong
            if (!Schema::hasColumn('users', 'department_id')) {
                $t->unsignedBigInteger('department_id')->nullable()->after('role');
                // Kalau mau FK dan tabel departments sudah ada:
                // $t->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users','department_id')) {
                // $t->dropForeign(['department_id']); // aman di-skip jika belum ada FK
                $t->dropColumn('department_id');
            }
            if (Schema::hasColumn('users','role')) {
                $t->dropColumn('role');
            }
        });
    }
};
