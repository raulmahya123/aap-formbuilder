<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SapPtoChecklistTemplate extends Model
{
    protected $table = 'sap_pto_checklist_templates';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SapPtoChecklistItem::class, 'template_id')->orderBy('sort_order')->orderBy('item_no');
    }

    public function records(): HasMany
    {
        return $this->hasMany(SapPtoRecord::class, 'template_id');
    }
}
