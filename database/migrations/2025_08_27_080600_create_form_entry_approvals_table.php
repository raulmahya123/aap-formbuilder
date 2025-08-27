<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('form_entry_approvals', function (Blueprint $t) {
      $t->id();
      $t->foreignId('form_entry_id')->constrained()->cascadeOnDelete();
      $t->foreignId('actor_id')->constrained('users')->cascadeOnDelete(); // siapa yang aksi
      $t->enum('action', ['review','approve','reject']);
      $t->text('notes')->nullable();
      $t->timestamps();
      $t->index(['form_entry_id','action']);
    });
  }
  public function down(): void { Schema::dropIfExists('form_entry_approvals'); }
};
