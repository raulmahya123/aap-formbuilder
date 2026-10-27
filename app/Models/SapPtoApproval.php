<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapPtoApproval extends Model
{
    protected $table = 'sap_pto_approvals';

    protected $fillable = [
        'pto_id',
        'actor_id',
        'action',
        'comments',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pto(): BelongsTo
    {
        return $this->belongsTo(SapPtoRecord::class, 'pto_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
