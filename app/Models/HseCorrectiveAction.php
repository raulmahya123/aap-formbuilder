<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseCorrectiveAction extends Model
{
    use SoftDeletes;

    protected $table = 'hse_corrective_actions';

    protected $fillable = [
        'action_no', 'source_type', 'source_id', 'source_module', 'site_id', 'department_id',
        'assigned_to', 'verified_by', 'title', 'description', 'priority', 'due_date',
        'status', 'evidence_path', 'verified_at', 'closed_at',
        'root_cause', 'corrective_action', 'preventive_action', 'rejection_reason', 'progress_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'verified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const STATUSES = ['Open', 'In Progress', 'Pending Verification', 'Closed', 'Overdue'];

    public function source()
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

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['Closed'])->whereDate('due_date', '<', now()->toDateString());
    }
}
