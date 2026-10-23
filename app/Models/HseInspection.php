<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseInspection extends Model
{
    use SoftDeletes;

    protected $table = 'hse_inspections';

    protected $fillable = [
        'inspection_no', 'site_id', 'department_id', 'inspector_id', 'title', 'type',
        'scheduled_date', 'completed_date', 'score', 'max_score', 'status', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'score' => 'float',
        'max_score' => 'float',
    ];

    public const STATUSES = ['Scheduled', 'In Progress', 'Completed', 'Overdue'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
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
