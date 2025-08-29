<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_participants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('qa_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->enum('role_label', ['user','admin','super_admin'])->nullable(); // snapshot role saat bergabung
            $t->boolean('is_muted')->default(false);
            $t->timestamp('last_read_at')->nullable();
            $t->timestamps();

            $t->unique(['thread_id','user_id']);
            $t->index(['user_id','thread_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_participants');
    }
};
