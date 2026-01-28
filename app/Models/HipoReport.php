<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HipoReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_id',
        'jobsite',
        'reporter_name',
        'report_time',
        'shift',
        'source',
        'category',
        'description',
        'potential_consequence',
        'risk_level',
        'stop_work',
        'control_engineering',
        'control_administrative',
        'control_work_practice',
        'control_ppe',
        'pic',
        'status',
        'evidence_file',
    ];

    protected $casts = [
        'report_time' => 'datetime',
        'stop_work' => 'boolean',
    ];

    // ================= RELATION =================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
