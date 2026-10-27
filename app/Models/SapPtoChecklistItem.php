<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SapPtoChecklistItem extends Model
{
    protected $table = 'sap_pto_checklist_items';

    protected $fillable = [
        'template_id',
        'section',
        'item_no',
        'question',
        'guidance',
        'answer_type',
        'expected_safe_answer',
        'unsafe_answer',
        'is_risk_indicator',
        'requires_follow_up',
        'has_apd_field',
        'has_other_field',
        'sub_options',
        'sort_order',
    ];

    protected $casts = [
        'is_risk_indicator' => 'boolean',
        'requires_follow_up' => 'boolean',
        'has_apd_field' => 'boolean',
        'has_other_field' => 'boolean',
        'sub_options' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SapPtoChecklistTemplate::class, 'template_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SapPtoAnswer::class, 'item_id');
    }
}
