<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapHazardApproval extends Model
{
    protected $table = 'sap_hazard_approvals';

    protected $fillable = [
        'hazard_id',
        'actor_id',
        'action',
        'comments',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(SapHazardReport::class, 'hazard_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
