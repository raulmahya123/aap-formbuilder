<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_acl', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $t->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $t->enum('perm',['view','edit','share','export','delete']);
            $t->timestamps();
            $t->unique(['document_id','user_id','department_id','perm'],'doc_acl_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('document_acl'); }
};
