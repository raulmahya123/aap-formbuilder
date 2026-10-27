<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapInspectionTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'sap_inspection_templates';

    protected $fillable = [
        'code', 'name', 'description', 'scoring_type', 'is_active', 'version', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function sections()
    {
        return $this->hasMany(SapInspectionSection::class, 'template_id')->orderBy('sort_order');
    }

    public function items()
    {
        return $this->hasMany(SapInspectionItem::class, 'template_id')->orderBy('sort_order');
    }

    public function inspections()
    {
        return $this->hasMany(SapInspection::class, 'template_id');
    }

    public function schedules()
    {
        return $this->hasMany(SapInspectionSchedule::class, 'template_id');
    }
}
