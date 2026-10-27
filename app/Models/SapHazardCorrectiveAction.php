<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapHazardCorrectiveAction extends Model
{
    protected $table = 'sap_hazard_corrective_actions';

    protected $fillable = [
        'hazard_id',
        'corrective_action',
        'pic_id',
        'target_completion_date',
        'actual_completion_date',
        'status',
        'evidence_notes',
        'submitted_by',
        'submitted_at',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(SapHazardReport::class, 'hazard_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
