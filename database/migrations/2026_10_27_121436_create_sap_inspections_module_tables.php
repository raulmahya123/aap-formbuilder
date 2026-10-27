<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Templates for 9 SHE-FM forms
        Schema::create('sap_inspection_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SHE-FM-005 etc
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('scoring_type', ['numeric_1_4', 'yes_no', 'mixed'])->default('numeric_1_4');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sap_inspection_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('sap_inspection_templates')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sap_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sap_inspection_sections')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('sap_inspection_templates')->cascadeOnDelete();
            $table->string('item_code')->nullable();
            $table->text('question');
            $table->text('guidance')->nullable();
            $table->enum('answer_type', ['score_1_4', 'yes_no_na', 'yes_no', 'text'])->default('score_1_4');
            $table->boolean('requires_evidence')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Schedules/targets linkage
        Schema::create('sap_inspection_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('sap_inspection_templates')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('period')->nullable(); // monthly/quarterly/yearly
            $table->unsignedInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->enum('status', ['Planned', 'In Progress', 'Completed', 'Cancelled'])->default('Planned');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Inspections
        Schema::create('sap_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no')->unique();
            $table->foreignId('template_id')->constrained('sap_inspection_templates')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('sap_inspection_schedules')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Under Review', 'Approved', 'Returned', 'Closed'])->default('Draft');
            $table->text('notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['template_id', 'status']);
            $table->index(['inspector_id', 'submitted_at']);
        });

        Schema::create('sap_inspection_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('sap_inspections')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('sap_inspection_items')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sap_inspection_sections')->cascadeOnDelete();
            $table->enum('answer', ['1', '2', '3', '4', 'YA', 'TIDAK', 'NA'])->nullable();
            $table->integer('score')->nullable(); // normalized
            $table->text('remarks')->nullable();
            $table->boolean('non_conformity')->default(false);
            $table->timestamps();

            $table->unique(['inspection_id', 'item_id']);
        });

        Schema::create('sap_inspection_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_no')->unique();
            $table->foreignId('inspection_id')->constrained('sap_inspections')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('sap_inspection_answers')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('sap_inspection_items')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('risk_class', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->enum('status', ['Open', 'In Progress', 'For Verification', 'Verified', 'Closed', 'Rejected'])->default('Open');
            $table->text('closure_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['inspection_id', 'status']);
            $table->index(['pic_id', 'status']);
        });

        Schema::create('sap_inspection_cas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('sap_inspection_findings')->cascadeOnDelete();
            $table->foreignId('inspection_id')->constrained('sap_inspections')->cascadeOnDelete();
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('corrective_action');
            $table->text('preventive_action')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['Planned', 'In Progress', 'Submitted', 'Verified', 'Rejected', 'Closed'])->default('Planned');
            $table->text('evidence_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_inspection_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sap_inspection_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('sap_inspections')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['Submitted', 'Reviewed', 'Approved', 'Returned', 'Closed', 'Revised']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_inspection_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('sap_inspections')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sap_inspection_logs');
        Schema::dropIfExists('sap_inspection_approvals');
        Schema::dropIfExists('sap_inspection_attachments');
        Schema::dropIfExists('sap_inspection_cas');
        Schema::dropIfExists('sap_inspection_findings');
        Schema::dropIfExists('sap_inspection_answers');
        Schema::dropIfExists('sap_inspections');
        Schema::dropIfExists('sap_inspection_schedules');
        Schema::dropIfExists('sap_inspection_items');
        Schema::dropIfExists('sap_inspection_sections');
        Schema::dropIfExists('sap_inspection_templates');
    }
};
