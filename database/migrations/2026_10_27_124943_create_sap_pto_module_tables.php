<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sap_pto_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('sap_pto_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('sap_pto_checklist_templates')->cascadeOnDelete();
            $table->string('section')->nullable();
            $table->unsignedInteger('item_no')->default(0);
            $table->text('question');
            $table->text('guidance')->nullable();
            $table->enum('answer_type', ['yes_no_na', 'text'])->default('yes_no_na');
            $table->string('expected_safe_answer')->nullable();
            $table->string('unsafe_answer')->nullable();
            $table->boolean('is_risk_indicator')->default(false);
            $table->boolean('requires_follow_up')->default(false);
            $table->boolean('has_apd_field')->default(false);
            $table->boolean('has_other_field')->default(false);
            $table->json('sub_options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sap_pto_records', function (Blueprint $table) {
            $table->id();
            $table->string('pto_no')->unique();
            $table->string('document_code')->default('SHE-FM-019');
            $table->foreignId('template_id')->nullable()->constrained('sap_pto_checklist_templates')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('work_activity')->nullable();
            $table->text('related_procedure')->nullable();
            $table->date('observation_date')->nullable();
            $table->time('observation_time')->nullable();
            $table->string('duration')->nullable();
            $table->string('work_location')->nullable();
            $table->foreignId('observer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('observed_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observer_nik')->nullable();
            $table->string('observer_position')->nullable();
            $table->string('observer_dept')->nullable();
            $table->string('observed_nik')->nullable();
            $table->string('observed_position')->nullable();
            $table->string('observed_dept')->nullable();
            $table->text('observation_description')->nullable();
            $table->text('safe_behavior')->nullable();
            $table->text('risky_behavior')->nullable();
            $table->text('unsafe_condition')->nullable();
            $table->text('potential_impact')->nullable();
            $table->text('immediate_action')->nullable();
            $table->date('immediate_action_date')->nullable();
            $table->foreignId('immediate_action_pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('immediate_action_status')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_dept')->nullable();
            $table->date('supervisor_review_date')->nullable();
            $table->text('supervisor_comment')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('follow_up_action')->nullable();
            $table->unsignedInteger('total_checklist')->default(0);
            $table->unsignedInteger('applicable_checklist')->default(0);
            $table->unsignedInteger('safe_observations')->default(0);
            $table->unsignedInteger('unsafe_observations')->default(0);
            $table->unsignedInteger('na_count')->default(0);
            $table->unsignedInteger('risky_behaviors_count')->default(0);
            $table->unsignedInteger('findings_count')->default(0);
            $table->decimal('safe_rate', 5, 2)->nullable();
            $table->decimal('unsafe_rate', 5, 2)->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Supervisor Review', 'Approved', 'Returned', 'Revised', 'Resubmitted', 'Closed'])->default('Draft');
            $table->text('review_notes')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('informed_to_observed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['observer_id', 'observation_date']);
            $table->index(['observed_employee_id', 'observation_date']);
        });

        Schema::create('sap_pto_observed_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pto_id')->constrained('sap_pto_records')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('nik')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_pto_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pto_id')->constrained('sap_pto_records')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('sap_pto_checklist_items')->cascadeOnDelete();
            $table->enum('answer', ['YA', 'TIDAK', 'NA'])->nullable();
            $table->boolean('is_safe')->default(true);
            $table->boolean('is_risky')->default(false);
            $table->json('apd_used')->nullable();
            $table->json('sub_selections')->nullable();
            $table->text('other_note')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('requires_follow_up')->default(false);
            $table->timestamps();

            $table->unique(['pto_id', 'item_id']);
        });

        Schema::create('sap_pto_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_no')->unique();
            $table->foreignId('pto_id')->constrained('sap_pto_records')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('sap_pto_answers')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('sap_pto_checklist_items')->nullOnDelete();
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
        });

        Schema::create('sap_pto_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sap_pto_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pto_id')->constrained('sap_pto_records')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['Submitted', 'Reviewed', 'Approved', 'Returned', 'Resubmitted', 'Closed']);
            $table->text('comments')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_pto_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pto_id')->constrained('sap_pto_records')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_pto_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('sap_pto_checklist_templates')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('observer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('period')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->enum('status', ['Planned', 'In Progress', 'Completed', 'Cancelled'])->default('Planned');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sap_pto_schedules');
        Schema::dropIfExists('sap_pto_activity_logs');
        Schema::dropIfExists('sap_pto_approvals');
        Schema::dropIfExists('sap_pto_attachments');
        Schema::dropIfExists('sap_pto_findings');
        Schema::dropIfExists('sap_pto_answers');
        Schema::dropIfExists('sap_pto_observed_employees');
        Schema::dropIfExists('sap_pto_records');
        Schema::dropIfExists('sap_pto_checklist_items');
        Schema::dropIfExists('sap_pto_checklist_templates');
    }
};
