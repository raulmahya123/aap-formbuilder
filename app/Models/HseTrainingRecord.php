<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HseTrainingRecord extends Model
{
    protected $table = 'hse_training_records';

    protected $fillable = [
        'course_id', 'user_id', 'site_id', 'status', 'scheduled_date',
        'completed_date', 'expires_at', 'score',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'expires_at' => 'date',
        'score' => 'float',
    ];

    public const STATUSES = ['Not Started', 'Scheduled', 'In Progress', 'Completed', 'Expired'];

    public function course()
    {
        return $this->belongsTo(HseTrainingCourse::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
