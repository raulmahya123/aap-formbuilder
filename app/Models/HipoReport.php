<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HipoReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_id',
        'jobsite',
        'reporter_name',
        'report_time',
        'jenis_hipo',
        'shift',
        'source',
        'category',
        'description',
        'potential_consequence',
        'risk_level',
        'kta',
        'tta',
        'stop_work',

        'control_engineering',
        'control_administrative',
        'control_work_practice',
        'control_ppe',

        'pic_engineering',
        'pic_administrative',
        'pic_work_practice',
        'pic_ppe',

        'evidence_engineering',
        'evidence_administrative',
        'evidence_work_practice',
        'evidence_ppe',

        'status',
        'admin_note',
    ];

    protected $casts = [
        'report_time' => 'datetime',
        'stop_work' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPicAttribute(): ?string
    {
        return $this->attributes['pic'] ?? $this->pic_engineering ?? $this->pic_administrative ?? $this->pic_work_practice ?? $this->pic_ppe;
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getJobsiteDisplayAttribute(): string
    {
        return $this->site?->displayLabel() ?? $this->jobsite ?? '—';
    }
}
