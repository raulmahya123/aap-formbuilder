<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessResponse extends Model
{
    protected $fillable = [
        'name',
        'email',
        'department',
        'mood',
        'stress_level',
        'sleep_hours',
        'physical_activity',
        'notes',
    ];

    protected $casts = [
        'sleep_hours' => 'decimal:1',
    ];
}
