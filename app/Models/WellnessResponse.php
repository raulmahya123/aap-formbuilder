<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessResponse extends Model
{
    protected $fillable = [
        'name',
        'nik',
        'position',
        'site',
        'shift',
        'sleep_before_shift',
        'sleep_quality',
        'sleep_quality_reason',
        'heavy_drowsiness',
        'health_complaint',
        'health_complaint_details',
        'drowsy_medication',
        'work_readiness',
    ];
}
