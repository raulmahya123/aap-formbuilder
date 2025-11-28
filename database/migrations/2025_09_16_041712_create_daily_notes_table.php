<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('daily_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // === Tambahan: relasi ke company & site (opsional) ===
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();

            $table->string('title');
            $table->text('content');
            $table->timestamp('note_time')->nullable(); // waktu input dicatat manual atau otomatis
            $table->timestamps();

            // FK existing
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // FK tambahan (set null saat parent dihapus)
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();

            // Index bantu untuk filter
            $table->index('company_id');
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_notes');
    }
};
