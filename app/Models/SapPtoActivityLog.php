<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapPtoActivityLog extends Model
{
    protected $table = 'sap_pto_activity_logs';

    protected $fillable = [
        'pto_id',
        'user_id',
        'action',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pto(): BelongsTo
    {
        return $this->belongsTo(SapPtoRecord::class, 'pto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
