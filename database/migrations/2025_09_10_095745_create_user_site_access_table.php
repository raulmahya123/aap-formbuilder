<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_site_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id','site_id']); // 1 user tidak boleh dobel akses site yg sama
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_site_access');
    }
};
