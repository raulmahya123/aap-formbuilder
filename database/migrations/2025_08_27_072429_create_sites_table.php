<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sites', function (Blueprint $t) {
            $t->id();
            $t->string('name',100);
            $t->string('code',20)->unique();   // HO, BGG, SBS, DBK
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sites');
    }
};
