<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseKpiTarget extends Model
{
    protected $table = 'hse_kpi_targets';

    protected $fillable = ['key', 'label', 'target_value', 'unit', 'direction', 'is_active'];

    protected $casts = [
        'target_value' => 'float',
        'is_active' => 'boolean',
    ];
}
