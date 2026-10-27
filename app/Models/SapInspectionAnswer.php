<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionAnswer extends Model
{
    protected $table = 'sap_inspection_answers';

    protected $fillable = [
        'inspection_id', 'item_id', 'section_id', 'answer', 'score', 'remarks', 'non_conformity'
    ];

    protected $casts = [
        'score' => 'integer',
        'non_conformity' => 'boolean',
    ];

    public function inspection()
    {
        return $this->belongsTo(SapInspection::class, 'inspection_id');
    }

    public function item()
    {
        return $this->belongsTo(SapInspectionItem::class, 'item_id');
    }

    public function section()
    {
        return $this->belongsTo(SapInspectionSection::class, 'section_id');
    }

    public function finding()
    {
        return $this->hasOne(SapInspectionFinding::class, 'answer_id');
    }
}
