<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('forms', function (Blueprint $t) {
      $t->id();
      $t->foreignId('department_id')->constrained()->cascadeOnDelete();
      $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();

      $t->string('title');
      $t->string('slug')->unique();

      // TANPA default
      $t->enum('doc_type', ['SOP','IK','FORM'])->index();

      $t->text('description')->nullable();

      $t->enum('type', ['builder','pdf']);
      $t->json('schema')->nullable();
      $t->string('pdf_path')->nullable();

      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('forms');
  }
};
