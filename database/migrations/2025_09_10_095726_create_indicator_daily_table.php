<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('indicator_daily', function (Blueprint $t) {
            $t->id();
            $t->foreignId('site_id')->constrained()->cascadeOnDelete();
            $t->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->decimal('value', 18, 4)->default(0);
            $t->text('note')->nullable();

            // 👇 tambahan untuk shift & keterlambatan
            $t->unsignedTinyInteger('shift')->nullable(); // 1 / 2 / null
            $t->timestamp('input_at')->nullable();        // kapan user submit
            $t->boolean('is_late')->default(false);       // true kalau di luar jam shift

            $t->timestamps();

            $t->unique(['site_id','indicator_id','date']);
            $t->index(['site_id','indicator_id','date']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('indicator_daily');
    }
};
