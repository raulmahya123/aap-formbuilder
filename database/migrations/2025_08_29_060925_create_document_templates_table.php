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

            // Pilih SALAH SATU: JSON (jika MariaDB/MySQL mendukung) ATAU longText

            // --- Opsi A: JSON (MySQL 5.7+/MariaDB 10.2+ biasanya ok)
            $t->json('blocks_config')->nullable();
            $t->json('layout_config')->nullable();
            $t->json('header_config')->nullable();
            $t->json('footer_config')->nullable();
            $t->json('signature_config')->nullable();

            // --- Opsi B: Kalau JSON bermasalah, comment Opsi A lalu pakai ini:
            // $t->longText('blocks_config')->nullable();
            // $t->longText('layout_config')->nullable();
            // $t->longText('header_config')->nullable();
            // $t->longText('footer_config')->nullable();
            // $t->longText('signature_config')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
