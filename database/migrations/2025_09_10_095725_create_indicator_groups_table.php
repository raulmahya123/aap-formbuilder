<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('indicator_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique(); // LAGGING, LEADING, DESC
            $t->unsignedInteger('order_index')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('indicator_groups');
    }
};
