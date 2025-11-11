<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $t) {
            $t->id();

            // Identitas site
            $t->string('name', 100);
            $t->string('code', 20)->unique(); // contoh: HO, BGG, SBS, DBK
            $t->text('description')->nullable();

            // Relasi ke companies (nullable biar data lama aman)
            $t->foreignId('company_id')
              ->nullable()
              ->constrained('companies')
              ->nullOnDelete();

            // Timestamps
            $t->timestamps();

            // (Opsional) index tambahan kalau sering filter per company
            $t->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
