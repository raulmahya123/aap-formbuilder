<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionSection extends Model
{
    protected $table = 'sap_inspection_sections';

    protected $fillable = ['template_id', 'code', 'title', 'description', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function template()
    {
        return $this->belongsTo(SapInspectionTemplate::class, 'template_id');
    }

    public function items()
    {
        return $this->hasMany(SapInspectionItem::class, 'section_id')->orderBy('sort_order');
    }

    public function answers()
    {
        return $this->hasMany(SapInspectionAnswer::class, 'section_id');
    }
}
