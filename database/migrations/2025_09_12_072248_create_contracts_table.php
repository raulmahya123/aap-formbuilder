<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('contracts', function (Blueprint $t) {
      $t->id();
      $t->string('title');
      $t->string('slug')->unique();
      $t->text('description')->nullable();
      $t->json('images');                           // simpan path foto
      $t->enum('visibility', ['whitelist','link','private'])->default('whitelist');
      $t->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
      $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
      $t->timestamp('expires_at')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('contracts');
  }
};

