<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sap_hazard_risk_matrix_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('probability');
            $table->unsignedTinyInteger('severity');
            $table->unsignedInteger('risk_score');
            $table->string('risk_level');
            $table->text('description')->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->text('required_action')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sap_hazard_reports', function (Blueprint $table) {
            $table->id();
            $table->string('hazard_no')->unique();
            $table->string('document_code')->default('SHE-FM-007');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reporter_nik')->nullable();
            $table->string('reporter_position')->nullable();
            $table->string('reporter_dept')->nullable();
            $table->date('report_date')->nullable();
            $table->time('report_time')->nullable();
            $table->string('location')->nullable();
            $table->string('location_detail')->nullable();
            $table->text('activity_related')->nullable();
            $table->text('hazard_description');
            $table->text('potential_impact')->nullable();
            $table->enum('hazard_type', ['Unsafe Action', 'Unsafe Condition', 'Environment', 'Equipment', 'Other'])->default('Unsafe Condition');
            $table->enum('status', ['Draft', 'Submitted', 'SHE Review', 'Assigned', 'In Progress', 'Pending Verification', 'Returned', 'Revised', 'Resubmitted', 'Reopened', 'Closed'])->default('Draft');
            $table->unsignedTinyInteger('probability')->nullable();
            $table->unsignedTinyInteger('severity')->nullable();
            $table->json('severity_dimensions')->nullable();
            $table->unsignedInteger('risk_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('risk_notes')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->text('work_stoppage_reason')->nullable();
            $table->boolean('work_stopped')->default(false);
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->foreignId('assigned_pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['reporter_id', 'report_date']);
            $table->index(['risk_level', 'status']);
            $table->index(['assigned_pic_id', 'status']);
        });

        Schema::create('sap_hazard_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('sap_hazard_reports')->cascadeOnDelete();
            $table->unsignedTinyInteger('probability');
            $table->unsignedTinyInteger('severity');
            $table->json('severity_dimensions')->nullable();
            $table->unsignedInteger('risk_score');
            $table->string('risk_level');
            $table->text('justification')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sap_hazard_temporary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('sap_hazard_reports')->cascadeOnDelete();
            $table->text('description');
            $table->date('action_date')->nullable();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sap_hazard_corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('sap_hazard_reports')->cascadeOnDelete();
            $table->text('corrective_action');
            $table->foreignId('pic_id')->constrained('users')->cascadeOnDelete();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['Assigned', 'In Progress', 'Submitted', 'Pending Verification', 'Verified', 'Rejected', 'Closed'])->default('Assigned');
            $table->text('evidence_notes')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_hazard_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sap_hazard_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('sap_hazard_reports')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['Submitted', 'Reviewed', 'Assigned', 'Returned', 'Resubmitted', 'Reopened', 'Verified', 'Closed']);
            $table->text('comments')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('sap_hazard_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('sap_hazard_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sap_hazard_activity_logs');
        Schema::dropIfExists('sap_hazard_approvals');
        Schema::dropIfExists('sap_hazard_attachments');
        Schema::dropIfExists('sap_hazard_corrective_actions');
        Schema::dropIfExists('sap_hazard_temporary_actions');
        Schema::dropIfExists('sap_hazard_risk_assessments');
        Schema::dropIfExists('sap_hazard_reports');
        Schema::dropIfExists('sap_hazard_risk_matrix_configs');
    }
};
