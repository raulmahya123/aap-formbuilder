<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapHazardActivityLog extends Model
{
    protected $table = 'sap_hazard_activity_logs';

    protected $fillable = [
        'hazard_id',
        'user_id',
        'action',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(SapHazardReport::class, 'hazard_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
