<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('qa_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->longText('body');
            $t->boolean('is_official_answer')->default(false); // admin/super_admin bisa tandai jawaban resmi
            $t->foreignId('parent_id')->nullable()->constrained('qa_messages')->nullOnDelete(); // reply tree (opsional)
            $t->json('attachments')->nullable(); // simpan path/file meta
            $t->timestamps();

            $t->index(['thread_id','created_at']);
            $t->index(['user_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_messages');
    }
};
