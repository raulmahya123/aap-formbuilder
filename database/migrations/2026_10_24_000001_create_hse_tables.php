<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hse_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_no')->nullable()->unique();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('investigator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('occurred_at');
            $table->dateTime('reported_at')->nullable();
            $table->string('category')->default('Incident');
            $table->string('type')->default('Other');
            $table->string('severity')->default('Medium');
            $table->string('potential_severity')->nullable();
            $table->string('location')->nullable();
            $table->string('activity')->nullable();
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->string('investigation_status')->default('Not Started');
            $table->unsignedInteger('lost_time_days')->default(0);
            $table->boolean('recordable')->default(false);
            $table->string('status')->default('Open');
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'occurred_at']);
            $table->index(['category', 'type']);
            $table->index(['status', 'severity']);
        });

        Schema::create('hse_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity');
            $table->text('hazard');
            $table->text('existing_controls')->nullable();
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('likelihood')->default(1);
            $table->unsignedTinyInteger('consequence')->default(1);
            $table->unsignedInteger('inherent_score')->default(1);
            $table->unsignedTinyInteger('residual_likelihood')->default(1);
            $table->unsignedTinyInteger('residual_consequence')->default(1);
            $table->unsignedInteger('residual_score')->default(1);
            $table->string('risk_level')->default('Low');
            $table->text('treatment')->nullable();
            $table->date('treatment_due_date')->nullable();
            $table->string('treatment_status')->default('Not Required');
            $table->string('status')->default('Active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['residual_score', 'risk_level']);
        });

        Schema::create('hse_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no')->nullable()->unique();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('Routine');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->string('status')->default('Scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
        });

        Schema::create('hse_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_no')->nullable()->unique();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('lead_auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('Internal');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('status')->default('Planned');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'scheduled_date']);
            $table->index(['status']);
        });

        Schema::create('hse_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_no')->nullable()->unique();
            $table->nullableMorphs('findingable');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('Medium');
            $table->string('status')->default('Open');
            $table->date('due_date')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['severity', 'status']);
            $table->index(['due_date', 'status']);
        });

        Schema::create('hse_corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->string('action_no')->nullable()->unique();
            $table->nullableMorphs('source');
            $table->string('source_module')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('Medium');
            $table->date('due_date')->nullable();
            $table->string('status')->default('Open');
            $table->string('evidence_path')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['priority', 'status']);
            $table->index(['due_date', 'status']);
        });

        Schema::create('hse_training_courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('Mandatory');
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('validity_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hse_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('hse_training_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('status')->default('Not Started');
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'course_id']);
            $table->index(['status', 'expires_at']);
            $table->index(['site_id', 'status']);
        });

        Schema::create('hse_environmental_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('recorded_date');
            $table->string('metric');
            $table->decimal('value', 15, 2)->default(0);
            $table->string('unit')->default('kg');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'recorded_date']);
            $table->index(['metric', 'recorded_date']);
        });

        Schema::create('hse_permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_no')->nullable()->unique();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('type');
            $table->string('issued_to')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('status')->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'valid_to']);
            $table->index(['status', 'valid_to']);
        });

        Schema::create('hse_exposure_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('hours', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'department_id', 'period_year', 'period_month'], 'hse_exposure_unique');
        });

        Schema::create('hse_kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('direction')->default('up');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hse_report_runs', function (Blueprint $table) {
            $table->id();
            $table->string('report_key');
            $table->string('title');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->json('filters')->nullable();
            $table->string('format')->default('csv');
            $table->string('status')->default('Completed');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['report_key', 'created_at']);
        });

        if (!Schema::hasColumn('sites', 'latitude')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('company_id');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_report_runs');
        Schema::dropIfExists('hse_kpi_targets');
        Schema::dropIfExists('hse_exposure_hours');
        Schema::dropIfExists('hse_permits');
        Schema::dropIfExists('hse_environmental_records');
        Schema::dropIfExists('hse_training_records');
        Schema::dropIfExists('hse_training_courses');
        Schema::dropIfExists('hse_corrective_actions');
        Schema::dropIfExists('hse_findings');
        Schema::dropIfExists('hse_audits');
        Schema::dropIfExists('hse_inspections');
        Schema::dropIfExists('hse_risk_assessments');
        Schema::dropIfExists('hse_incidents');
    }
};
