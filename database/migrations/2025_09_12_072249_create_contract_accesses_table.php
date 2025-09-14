<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('contract_accesses', function (Blueprint $t) {
      $t->id();
      $t->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
      $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->string('email')->index();
      $t->enum('status', ['pending','approved','blocked'])->default('approved');
      $t->timestamp('verified_at')->nullable();
      $t->timestamps();
      $t->unique(['contract_id','email']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('contract_accesses');
  }
};

