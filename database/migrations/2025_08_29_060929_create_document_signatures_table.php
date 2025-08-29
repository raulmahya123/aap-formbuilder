<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_signatures', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $t->string('role'); // Disiapkan/Diperiksa/Disetujui/Ditetapkan
            $t->string('name')->nullable();
            $t->string('position_title')->nullable();
            $t->string('image_path')->nullable(); // path file ttd/paraf
            $t->unsignedInteger('order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('document_signatures'); }
};
