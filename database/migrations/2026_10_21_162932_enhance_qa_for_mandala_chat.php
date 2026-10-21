<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Perkaya qa_threads (Conversations)
        Schema::table('qa_threads', function (Blueprint $t) {
            if (!Schema::hasColumn('qa_threads', 'type')) {
                // personal, group, site
                $t->string('type', 20)->default('group')->after('subject');
            }
            if (!Schema::hasColumn('qa_threads', 'group_type')) {
                // site, department, project, general
                $t->string('group_type', 30)->nullable()->after('type');
            }
            if (!Schema::hasColumn('qa_threads', 'description')) {
                $t->text('description')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('qa_threads', 'avatar_path')) {
                $t->string('avatar_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('qa_threads', 'company_id')) {
                $t->foreignId('company_id')->nullable()->after('site_id')->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('qa_threads', 'direct_hash')) {
                // Hash unik untuk 1-1 chat: min(user_a, user_b) . '_' . max(user_a, user_b)
                $t->string('direct_hash', 64)->nullable()->unique()->after('avatar_path');
            }
        });

        // 2. Perkaya qa_participants (Conversation Members)
        Schema::table('qa_participants', function (Blueprint $t) {
            if (!Schema::hasColumn('qa_participants', 'is_pinned')) {
                $t->boolean('is_pinned')->default(false)->after('is_muted');
            }
            if (!Schema::hasColumn('qa_participants', 'pinned_at')) {
                $t->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            if (!Schema::hasColumn('qa_participants', 'is_archived')) {
                $t->boolean('is_archived')->default(false)->after('pinned_at');
            }
            if (!Schema::hasColumn('qa_participants', 'chat_role')) {
                // member, admin, owner
                $t->string('chat_role', 20)->default('member')->after('role_label');
            }
        });

        // 3. Perkaya qa_messages (Messages)
        Schema::table('qa_messages', function (Blueprint $t) {
            if (!Schema::hasColumn('qa_messages', 'message_type')) {
                // text, report, system
                $t->string('message_type', 20)->default('text')->after('body');
            }
            if (!Schema::hasColumn('qa_messages', 'edited_at')) {
                $t->timestamp('edited_at')->nullable()->after('attachments');
            }
            if (!Schema::hasColumn('qa_messages', 'deleted_at')) {
                $t->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('qa_messages', 'forwarded_from_id')) {
                $t->foreignId('forwarded_from_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('qa_messages', 'pinned_at')) {
                $t->timestamp('pinned_at')->nullable();
            }
            if (!Schema::hasColumn('qa_messages', 'pinned_by')) {
                $t->foreignId('pinned_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // 4. Tabel Chat Reports (Structured Reporting di Chat)
        if (!Schema::hasTable('chat_reports')) {
            Schema::create('chat_reports', function (Blueprint $t) {
                $t->id();
                $t->foreignId('thread_id')->constrained('qa_threads')->cascadeOnDelete();
                $t->foreignId('message_id')->nullable()->constrained('qa_messages')->nullOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->string('title', 255);
                $t->string('report_type', 50); // safety, maintenance, operasional, logistik, lainnya
                $t->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
                $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $t->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $t->text('description');
                $t->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $t->json('metadata')->nullable();
                $t->timestamps();

                $t->index(['thread_id', 'status']);
                $t->index(['site_id', 'status']);
            });
        }

        // 5. Tabel Chat Report Status Histories (Audit trail perubahan status laporan)
        if (!Schema::hasTable('chat_report_status_histories')) {
            Schema::create('chat_report_status_histories', function (Blueprint $t) {
                $t->id();
                $t->foreignId('report_id')->constrained('chat_reports')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->string('from_status', 30);
                $t->string('to_status', 30);
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        // 6. Tabel Reactions
        if (!Schema::hasTable('chat_message_reactions')) {
            Schema::create('chat_message_reactions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('message_id')->constrained('qa_messages')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->string('reaction', 32); // emoji char: 👍, ❤️, dsb
                $t->timestamps();

                $t->unique(['message_id', 'user_id', 'reaction']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reactions');
        Schema::dropIfExists('chat_report_status_histories');
        Schema::dropIfExists('chat_reports');

        Schema::table('qa_messages', function (Blueprint $t) {
            $t->dropColumn(['message_type', 'edited_at', 'deleted_at', 'forwarded_from_id', 'pinned_at', 'pinned_by']);
        });

        Schema::table('qa_participants', function (Blueprint $t) {
            $t->dropColumn(['is_pinned', 'pinned_at', 'is_archived', 'chat_role']);
        });

        Schema::table('qa_threads', function (Blueprint $t) {
            $t->dropForeign(['company_id']);
            $t->dropColumn(['type', 'group_type', 'description', 'avatar_path', 'company_id', 'direct_hash']);
        });
    }
};
