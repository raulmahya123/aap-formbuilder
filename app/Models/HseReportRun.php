<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseReportRun extends Model
{
    protected $table = 'hse_report_runs';

    protected $fillable = [
        'report_key', 'title', 'period_from', 'period_to', 'filters',
        'format', 'status', 'generated_by', 'file_path',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'filters' => 'array',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
