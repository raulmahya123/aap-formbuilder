<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheKpiTarget extends Model
{
    use HasFactory;

    protected $table = 'she_kpi_targets';

    protected $fillable = [
        'kpi_key',
        'site_id',
        'year',
        'month',
        'target_value',
        'unit',
        'direction',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'target_value' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
