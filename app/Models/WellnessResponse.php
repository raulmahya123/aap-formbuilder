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
        'health_complaint',
        'drowsy_medication',
        'work_readiness',
    ];
}
