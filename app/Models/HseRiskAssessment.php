<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseRiskAssessment extends Model
{
    use SoftDeletes;

    protected $table = 'hse_risk_assessments';

    protected $fillable = [
        'site_id', 'department_id', 'owner_id', 'activity', 'hazard', 'existing_controls',
        'category', 'likelihood', 'consequence', 'inherent_score',
        'residual_likelihood', 'residual_consequence', 'residual_score', 'risk_level',
        'treatment', 'treatment_due_date', 'treatment_status', 'status',
    ];

    protected $casts = [
        'likelihood' => 'integer',
        'consequence' => 'integer',
        'inherent_score' => 'integer',
        'residual_likelihood' => 'integer',
        'residual_consequence' => 'integer',
        'residual_score' => 'integer',
        'treatment_due_date' => 'date',
    ];

    public const LEVELS = ['Low', 'Medium', 'High', 'Extreme'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function actions()
    {
        return $this->morphMany(HseCorrectiveAction::class, 'source');
    }

    public static function levelForScore(int $score): string
    {
        return match (true) {
            $score >= 15 => 'Extreme',
            $score >= 9 => 'High',
            $score >= 4 => 'Medium',
            default => 'Low',
        };
    }

    public static function colorForLevel(string $level): string
    {
        return match ($level) {
            'Extreme' => 'bg-[#7F1D1D] text-white',
            'High' => 'bg-[#DC2626] text-white',
            'Medium' => 'bg-[#F59E0B] text-[#172033]',
            default => 'bg-[#16A34A] text-white',
        };
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
