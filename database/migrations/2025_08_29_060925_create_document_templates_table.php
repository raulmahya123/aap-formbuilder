<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name'); // SOP, IK, ST, dll

            // Tambahan untuk foto/logo template
            $t->string('photo_path')->nullable(); // simpan path file (storage/app/public/...)

            // Konfigurasi JSON
            $t->json('blocks_config')->nullable();
            $t->json('layout_config')->nullable();
            $t->json('header_config')->nullable();
            $t->json('footer_config')->nullable();
            $t->json('signature_config')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
