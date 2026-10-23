<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SHE Manhours Management
        if (!Schema::hasTable('she_manhours')) {
            Schema::create('she_manhours', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->unsignedInteger('employee_count')->default(0);
                $table->decimal('manhours', 12, 2)->default(0);
                $table->decimal('lost_hours', 12, 2)->default(0);
                $table->string('status', 30)->default('Submitted'); // Draft, Submitted, Validated, Returned
                $table->text('notes')->nullable();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('validated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['date', 'site_id', 'status']);
                $table->index(['site_id', 'company_id']);
            });
        }

        // 2. SHE KPI Targets
        if (!Schema::hasTable('she_kpi_targets')) {
            Schema::create('she_kpi_targets', function (Blueprint $table) {
                $table->id();
                $table->string('kpi_key', 50); // fatality, lti, ltifr, ltisr, pd, pdfr, etc.
                $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month')->nullable(); // null for annual target
                $table->decimal('target_value', 15, 2)->default(0);
                $table->string('unit', 30)->nullable();
                $table->string('direction', 10)->default('lower'); // lower or higher
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['kpi_key', 'site_id', 'year', 'month'], 'she_kpi_target_unique');
            });
        }

        // 3. SAP Targets (Safety Accountability Program)
        if (!Schema::hasTable('she_sap_targets')) {
            Schema::create('she_sap_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('target_inspection')->default(0);
                $table->unsignedInteger('target_pto')->default(0);
                $table->unsignedInteger('target_hazard')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['year', 'month', 'site_id']);
                $table->index(['user_id', 'year', 'month']);
            });
        }

        // 4. Notifications Center
        if (!Schema::hasTable('she_notifications')) {
            Schema::create('she_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('type', 50)->default('general'); // pica_assigned, pica_reminder, submission_returned, verification_request, doc_review
                $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
                $table->string('link')->nullable();
                $table->boolean('is_read')->default(false);
                $table->dateTime('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('she_notifications');
        Schema::dropIfExists('she_sap_targets');
        Schema::dropIfExists('she_kpi_targets');
        Schema::dropIfExists('she_manhours');
    }
};
