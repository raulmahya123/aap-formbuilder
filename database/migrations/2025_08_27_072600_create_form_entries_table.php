<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('form_entries', function (Blueprint $t) {
      $t->id();
      $t->foreignId('form_id')->constrained()->cascadeOnDelete();
      $t->foreignId('user_id')->constrained()->cascadeOnDelete();
      $t->json('data');                           // data isian user (untuk builder/pdf)
      $t->string('pdf_output_path')->nullable();  // hasil render PDF jawaban (opsional)
      $t->timestamps();
      $t->index(['form_id','user_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('form_entries'); }
};
