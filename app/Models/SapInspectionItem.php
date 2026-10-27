<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionItem extends Model
{
    protected $table = 'sap_inspection_items';

    protected $fillable = [
        'section_id', 'template_id', 'item_code', 'question', 'guidance',
        'answer_type', 'requires_evidence', 'is_critical', 'sort_order'
    ];

    protected $casts = [
        'requires_evidence' => 'boolean',
        'is_critical' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(SapInspectionSection::class, 'section_id');
    }

    public function template()
    {
        return $this->belongsTo(SapInspectionTemplate::class, 'template_id');
    }

    public function answers()
    {
        return $this->hasMany(SapInspectionAnswer::class, 'item_id');
    }
}
