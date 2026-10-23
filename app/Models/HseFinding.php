<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseFinding extends Model
{
    use SoftDeletes;

    protected $table = 'hse_findings';

    protected $fillable = [
        'finding_no', 'findingable_type', 'findingable_id', 'site_id', 'department_id',
        'assigned_to', 'title', 'description', 'severity', 'status', 'due_date', 'closed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public const SEVERITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const STATUSES = ['Open', 'In Progress', 'Closed', 'Overdue'];

    public function findingable()
    {
        return $this->morphTo();
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function actions()
    {
        return $this->morphMany(HseCorrectiveAction::class, 'source');
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
