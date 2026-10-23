<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseExposureHour extends Model
{
    protected $table = 'hse_exposure_hours';

    protected $fillable = ['site_id', 'department_id', 'period_year', 'period_month', 'hours'];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'hours' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
