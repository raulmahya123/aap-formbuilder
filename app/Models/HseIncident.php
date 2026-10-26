<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseIncident extends Model
{
    use SoftDeletes;

    protected $table = 'hse_incidents';

    protected $fillable = [
        'incident_no', 'site_id', 'department_id', 'reported_by', 'investigator_id',
        'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at',
        'occurred_at', 'reported_at', 'category', 'type', 'severity', 'potential_severity',
        'location', 'activity', 'description', 'immediate_action', 'root_cause',
        'corrective_action', 'preventive_action', 'investigation_status',
        'lost_time_days', 'property_damage_cost', 'injured_name', 'injury_type',
        'environmental_impact', 'evidence_path', 'recordable', 'status', 'closed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'recordable' => 'boolean',
        'lost_time_days' => 'integer',
        'property_damage_cost' => 'float',
    ];

    public const CATEGORIES = ['Incident', 'Near Miss'];

    public const TYPES = [
        'Fatality', 'Lost Time Injury', 'Restricted Work Case', 'Medical Treatment Case',
        'First Aid Case', 'Property Damage', 'Environmental', 'PAK', 'KAPTK', 'Near Miss', 'Other',
    ];

    public const SEVERITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const INVESTIGATION_STATUSES = ['Not Started', 'In Progress', 'Completed'];

    public const STATUSES = ['Draft', 'Reported', 'Under Investigation', 'Reviewed', 'Approved', 'Closed'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reportedBy()
    {
        return $this->reporter();
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function actions()
    {
        return $this->morphMany(HseCorrectiveAction::class, 'source');
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }

    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }
}
