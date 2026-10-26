<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance hse_incidents
        if (Schema::hasTable('hse_incidents')) {
            Schema::table('hse_incidents', function (Blueprint $table) {
                if (!Schema::hasColumn('hse_incidents', 'immediate_action')) {
                    $table->text('immediate_action')->nullable()->after('description');
                }
                if (!Schema::hasColumn('hse_incidents', 'corrective_action')) {
                    $table->text('corrective_action')->nullable()->after('root_cause');
                }
                if (!Schema::hasColumn('hse_incidents', 'preventive_action')) {
                    $table->text('preventive_action')->nullable()->after('corrective_action');
                }
                if (!Schema::hasColumn('hse_incidents', 'property_damage_cost')) {
                    $table->decimal('property_damage_cost', 15, 2)->default(0)->after('lost_time_days');
                }
                if (!Schema::hasColumn('hse_incidents', 'injured_name')) {
                    $table->string('injured_name')->nullable()->after('property_damage_cost');
                }
                if (!Schema::hasColumn('hse_incidents', 'injury_type')) {
                    $table->string('injury_type')->nullable()->after('injured_name');
                }
                if (!Schema::hasColumn('hse_incidents', 'environmental_impact')) {
                    $table->text('environmental_impact')->nullable()->after('injury_type');
                }
                if (!Schema::hasColumn('hse_incidents', 'evidence_path')) {
                    $table->string('evidence_path')->nullable()->after('environmental_impact');
                }
                if (!Schema::hasColumn('hse_incidents', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('investigator_id');
                }
                if (!Schema::hasColumn('hse_incidents', 'reviewed_at')) {
                    $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
                }
                if (!Schema::hasColumn('hse_incidents', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_at');
                }
                if (!Schema::hasColumn('hse_incidents', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        // 2. Enhance hse_risk_assessments
        if (Schema::hasTable('hse_risk_assessments')) {
            Schema::table('hse_risk_assessments', function (Blueprint $table) {
                if (!Schema::hasColumn('hse_risk_assessments', 'risk_no')) {
                    $table->string('risk_no', 50)->nullable()->after('id');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'version')) {
                    $table->string('version', 20)->default('1.0')->after('risk_no');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('owner_id');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'reviewed_at')) {
                    $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'review_notes')) {
                    $table->text('review_notes')->nullable()->after('reviewed_at');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('review_notes');
                }
                if (!Schema::hasColumn('hse_risk_assessments', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        // 3. Enhance forms with explicit she_integration_type
        if (Schema::hasTable('forms')) {
            Schema::table('forms', function (Blueprint $table) {
                if (!Schema::hasColumn('forms', 'she_integration_type')) {
                    $table->string('she_integration_type', 50)->nullable()->after('type'); // incident, inspection, pto, hazard, audit, risk, ccm, none
                }
            });
        }

        // 4. Enhance hse_corrective_actions
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
                    $table->text('rejection_reason')->nullable()->after('preventive_action');
                }
                if (!Schema::hasColumn('hse_corrective_actions', 'progress_notes')) {
                    $table->text('progress_notes')->nullable()->after('rejection_reason');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('forms')) {
            Schema::table('forms', function (Blueprint $table) {
                if (Schema::hasColumn('forms', 'she_integration_type')) {
                    $table->dropColumn('she_integration_type');
                }
            });
        }

        if (Schema::hasTable('hse_risk_assessments')) {
            Schema::table('hse_risk_assessments', function (Blueprint $table) {
                $cols = ['risk_no', 'version', 'reviewed_by', 'reviewed_at', 'review_notes', 'approved_by', 'approved_at'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('hse_risk_assessments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('hse_incidents')) {
            Schema::table('hse_incidents', function (Blueprint $table) {
                $cols = [
                    'immediate_action', 'corrective_action', 'preventive_action', 'property_damage_cost',
                    'injured_name', 'injury_type', 'environmental_impact', 'evidence_path',
                    'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('hse_incidents', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
