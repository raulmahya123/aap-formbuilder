<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapPtoRecord extends Model
{
    use SoftDeletes;

    protected $table = 'sap_pto_records';

    protected $fillable = [
        'pto_no',
        'document_code',
        'template_id',
        'site_id',
        'department_id',
        'work_activity',
        'related_procedure',
        'observation_date',
        'observation_time',
        'duration',
        'work_location',
        'observer_id',
        'observed_employee_id',
        'observer_nik',
        'observer_position',
        'observer_dept',
        'observed_nik',
        'observed_position',
        'observed_dept',
        'observation_description',
        'safe_behavior',
        'risky_behavior',
        'unsafe_condition',
        'potential_impact',
        'immediate_action',
        'immediate_action_date',
        'immediate_action_pic_id',
        'immediate_action_status',
        'supervisor_id',
        'supervisor_name',
        'supervisor_dept',
        'supervisor_review_date',
        'supervisor_comment',
        'recommendation',
        'follow_up_action',
        'total_checklist',
        'applicable_checklist',
        'safe_observations',
        'unsafe_observations',
        'na_count',
        'risky_behaviors_count',
        'findings_count',
        'safe_rate',
        'unsafe_rate',
        'status',
        'review_notes',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'reviewed_by',
        'approved_by',
        'informed_to_observed',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'immediate_action_date' => 'date',
        'supervisor_review_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'informed_to_observed' => 'boolean',
        'safe_rate' => 'decimal:2',
        'unsafe_rate' => 'decimal:2',
    ];

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function observedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observed_employee_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SapPtoChecklistTemplate::class, 'template_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SapPtoAnswer::class, 'pto_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(SapPtoFinding::class, 'pto_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SapPtoApproval::class, 'pto_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SapPtoActivityLog::class, 'pto_id');
    }

    public function observedList(): HasMany
    {
        return $this->hasMany(SapPtoObservedEmployee::class, 'pto_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SapPtoAttachment::class, 'attachable_id')->where('attachable_type', self::class);
    }
}
