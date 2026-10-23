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
        'occurred_at', 'reported_at', 'category', 'type', 'severity', 'potential_severity',
        'location', 'activity', 'description', 'root_cause', 'investigation_status',
        'lost_time_days', 'recordable', 'status', 'closed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'reported_at' => 'datetime',
        'closed_at' => 'datetime',
        'recordable' => 'boolean',
        'lost_time_days' => 'integer',
    ];

    public const CATEGORIES = ['Incident', 'Near Miss'];

    public const TYPES = [
        'First Aid Case', 'Medical Treatment Case', 'Restricted Work Case',
        'Lost Time Injury', 'Fatality', 'Property Damage', 'Environmental', 'Other',
    ];

    public const SEVERITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const INVESTIGATION_STATUSES = ['Not Started', 'In Progress', 'Completed'];

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

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigator_id');
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
