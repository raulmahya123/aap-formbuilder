<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapPtoAnswer extends Model
{
    protected $table = 'sap_pto_answers';

    protected $fillable = [
        'pto_id',
        'item_id',
        'answer',
        'is_safe',
        'is_risky',
        'apd_used',
        'sub_selections',
        'other_note',
        'remarks',
        'requires_follow_up',
    ];

    protected $casts = [
        'is_safe' => 'boolean',
        'is_risky' => 'boolean',
        'apd_used' => 'array',
        'sub_selections' => 'array',
        'requires_follow_up' => 'boolean',
    ];

    public function pto(): BelongsTo
    {
        return $this->belongsTo(SapPtoRecord::class, 'pto_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SapPtoChecklistItem::class, 'item_id');
    }
}
