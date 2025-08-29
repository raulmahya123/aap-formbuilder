<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name');                  // SOP, IK, ST, dll
            $t->json('header_config')->nullable();
            $t->json('footer_config')->nullable();
            $t->json('signature_config')->nullable();
            $t->json('layout_config')->nullable();  // margins/font/grid
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('document_templates'); }
};
