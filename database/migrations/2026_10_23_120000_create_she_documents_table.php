<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('she_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code')->unique();
            $table->string('title');
            $table->string('category'); // Policy, SOP, IK, Form, Other
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('version')->default('1.0');
            $table->integer('revision_number')->default(0);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->string('status')->default('Draft'); // Draft, Submitted, In Review, Approved, Published, Obsolete
            $table->text('rejection_reason')->nullable();
            $table->json('access_roles')->nullable(); // e.g., ["super_admin", "admin", "she_officer"]
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['review_date']);
        });

        Schema::create('she_document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('she_documents')->cascadeOnDelete();
            $table->integer('revision_number');
            $table->string('version');
            $table->string('file_path')->nullable();
            $table->text('change_summary')->nullable();
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add root_cause, corrective_action, preventive_action columns to hse_corrective_actions if missing
        if (Schema::hasTable('hse_corrective_actions')) {
            Schema::table('hse_corrective_actions', function (Blueprint $table) {
                if (!Schema::hasColumn('hse_corrective_actions', 'root_cause')) {
                    $table->text('root_cause')->nullable()->after('description');
                }
                if (!Schema::hasColumn('hse_corrective_actions', 'corrective_action')) {
                    $table->text('corrective_action')->nullable()->after('root_cause');
                }
                if (!Schema::hasColumn('hse_corrective_actions', 'preventive_action')) {
                    $table->text('preventive_action')->nullable()->after('corrective_action');
                }
                if (!Schema::hasColumn('hse_corrective_actions', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('evidence_path');
                }
                if (!Schema::hasColumn('hse_corrective_actions', 'progress_notes')) {
                    $table->text('progress_notes')->nullable()->after('rejection_reason');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('she_document_revisions');
        Schema::dropIfExists('she_documents');

        if (Schema::hasTable('hse_corrective_actions')) {
            Schema::table('hse_corrective_actions', function (Blueprint $table) {
                $cols = ['root_cause', 'corrective_action', 'preventive_action', 'rejection_reason', 'progress_notes'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('hse_corrective_actions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
