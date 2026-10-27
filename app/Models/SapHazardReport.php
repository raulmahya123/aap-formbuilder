<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapHazardReport extends Model
{
    use SoftDeletes;

    protected $table = 'sap_hazard_reports';

    protected $fillable = [
        'hazard_no',
        'document_code',
        'site_id',
        'department_id',
        'reporter_id',
        'reporter_nik',
        'reporter_position',
        'reporter_dept',
        'report_date',
        'report_time',
        'location',
        'location_detail',
        'activity_related',
        'hazard_description',
        'potential_impact',
        'hazard_type',
        'status',
        'probability',
        'severity',
        'severity_dimensions',
        'risk_score',
        'risk_level',
        'risk_notes',
        'is_critical',
        'work_stoppage_reason',
        'work_stopped',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'assigned_pic_id',
        'assigned_at',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
        'severity_dimensions' => 'array',
        'is_critical' => 'boolean',
        'work_stopped' => 'boolean',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignedPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(SapHazardRiskAssessment::class, 'hazard_id');
    }

    public function temporaryActions(): HasMany
    {
        return $this->hasMany(SapHazardTemporaryAction::class, 'hazard_id');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(SapHazardCorrectiveAction::class, 'hazard_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SapHazardApproval::class, 'hazard_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SapHazardActivityLog::class, 'hazard_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SapHazardAttachment::class, 'attachable_id')->where('attachable_type', self::class);
    }
}
