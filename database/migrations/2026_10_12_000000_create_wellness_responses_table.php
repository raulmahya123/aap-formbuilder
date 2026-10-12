<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wellness_responses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('department')->nullable();
            $table->string('mood', 30);
            $table->unsignedTinyInteger('stress_level');
            $table->decimal('sleep_hours', 3, 1)->nullable();
            $table->string('physical_activity', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'mood']);
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_responses');
    }
};
