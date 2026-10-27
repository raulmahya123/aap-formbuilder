<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapHazardRiskAssessment extends Model
{
    protected $table = 'sap_hazard_risk_assessments';

    protected $fillable = [
        'hazard_id',
        'probability',
        'severity',
        'severity_dimensions',
        'risk_score',
        'risk_level',
        'justification',
        'assessed_by',
    ];

    protected $casts = [
        'severity_dimensions' => 'array',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(SapHazardReport::class, 'hazard_id');
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
