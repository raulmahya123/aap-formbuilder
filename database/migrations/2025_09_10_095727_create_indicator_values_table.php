<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('indicator_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('site_id')->constrained()->cascadeOnDelete();
            $t->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('month');
            $t->decimal('value', 18, 4)->default(0);
            $t->timestamps();
            $t->unique(['site_id','indicator_id','year','month']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('indicator_values');
    }
};
