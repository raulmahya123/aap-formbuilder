<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('form_entry_files', function (Blueprint $t) {
      $t->id();
      $t->foreignId('form_entry_id')->constrained()->cascadeOnDelete();
      $t->string('field_name');             // nama field file pada schema
      $t->string('original_name');
      $t->string('mime')->nullable();
      $t->unsignedBigInteger('size')->default(0);
      $t->string('path');                   // path di storage (disk public)
      $t->timestamps();
      $t->index(['form_entry_id','field_name']);
    });
  }
  public function down(): void { Schema::dropIfExists('form_entry_files'); }
};
