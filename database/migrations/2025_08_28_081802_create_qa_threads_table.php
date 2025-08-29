<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_threads', function (Blueprint $t) {
            $t->id();
            $t->string('subject');
            $t->enum('scope', ['public', 'private'])->default('public'); // publik / 1-1
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); // opsional
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->enum('status', ['open','resolved','archived'])->default('open');
            $t->timestamp('last_message_at')->nullable()->index();
            $t->timestamps();

            $t->index(['scope','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_threads');
    }
};
