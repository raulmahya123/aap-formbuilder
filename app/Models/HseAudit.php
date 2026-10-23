<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseAudit extends Model
{
    use SoftDeletes;

    protected $table = 'hse_audits';

    protected $fillable = [
        'audit_no', 'site_id', 'department_id', 'lead_auditor_id', 'title', 'type',
        'scheduled_date', 'completed_date', 'status', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public const STATUSES = ['Planned', 'In Progress', 'Completed'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leadAuditor()
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function findings()
    {
        return $this->morphMany(HseFinding::class, 'findingable');
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
