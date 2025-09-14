<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('contracts', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->string('file_path');
    $table->unsignedBigInteger('size_bytes')->nullable();
    $table->string('mime')->default('application/pdf');
    $table->timestamps();
});
  }
  public function down(): void {
    Schema::dropIfExists('contracts');
  }
};

