<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('indicators', function (Blueprint $t) {
            $t->id();
            $t->foreignId('indicator_group_id')->constrained()->cascadeOnDelete();

            $t->string('name');
            $t->string('code')->unique();       // e.g. LTI, MAN_HOURS
            $t->enum('data_type', ['int','decimal','currency','rate'])->default('decimal');
            $t->enum('agg', ['sum','avg','max','min'])->default('sum'); // agregasi bulanan
            $t->string('unit', 50)->nullable(); // orang, jam, kasus, Rp, %
            $t->string('threshold', 100)->nullable(); // ✅ cukup begini
            $t->unsignedInteger('order_index')->default(0);
            $t->boolean('is_derived')->default(false);
            $t->text('formula')->nullable();    // mis: "LTI / MAN_HOURS * 1e6"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('indicators');
    }
};
