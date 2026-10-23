<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HseEnvironmentalRecord extends Model
{
    protected $table = 'hse_environmental_records';

    protected $fillable = [
        'site_id', 'department_id', 'recorded_by', 'recorded_date', 'metric', 'value', 'unit', 'notes',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'value' => 'float',
    ];

    public const METRICS = [
        'Waste Generated', 'Hazardous Waste', 'Non-Hazardous Waste', 'Recycled Waste',
        'Energy Consumption', 'Water Consumption', 'Fuel Consumption',
    ];

    public const WASTE_METRICS = ['Waste Generated', 'Hazardous Waste', 'Non-Hazardous Waste', 'Recycled Waste'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
