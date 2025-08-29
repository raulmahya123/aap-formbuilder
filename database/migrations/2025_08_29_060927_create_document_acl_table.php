<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_acls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $t->string('perm', 20); // fleksibel, bisa ganti enum kalau fix
            $t->timestamps();

            $t->unique(['document_id','user_id','department_id','perm'],'doc_acl_unique');
            $t->index('perm');

            $t->engine = 'InnoDB';
            $t->charset = 'utf8mb4';
            $t->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void {
        Schema::dropIfExists('document_acls');
    }
};
