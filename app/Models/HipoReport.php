<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HipoReport extends Model
{
    use HasFactory;

    /**
     * ==========================
     * MASS ASSIGNMENT
     * ==========================
     */
    protected $fillable = [
        // RELASI
        'user_id',
        'site_id',

        // DATA UMUM LAPORAN
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

        // PIC
        'pic', // PIC utama

        // KONTROL RISIKO
        'control_engineering',
        'control_administrative',
        'control_work_practice',
        'control_ppe',

        // PIC PER KONTROL
        'pic_engineering',
        'pic_administrative',
        'pic_work_practice',
        'pic_ppe',

        // EVIDENCE PER KONTROL
        'evidence_engineering',
        'evidence_administrative',
        'evidence_work_practice',
        'evidence_ppe',

        // STATUS & CATATAN
        'status',
        'admin_note',
    ];

    /**
     * ==========================
     * CASTING
     * ==========================
     */
    protected $casts = [
        'report_time' => 'datetime',
        'stop_work' => 'boolean',
    ];

    /**
     * ==========================
     * RELATIONSHIP
     * ==========================
     */

    // User pelapor
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Site / Jobsite (opsional relasi)
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * ==========================
     * ACCESSOR (OPTIONAL)
     * ==========================
     */

    // Badge warna status (buat UI)
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'Open' => 'red',
            'On Progress' => 'yellow',
            'Closed' => 'green',
            'Rejected' => 'gray',
            default => 'blue',
        };
    }

    /**
     * ==========================
     * HELPER
     * ==========================
     */

    // Semua evidence dalam array (mudah dipakai di view)
    public function evidences()
    {
        return [
            'engineering' => $this->evidence_engineering,
            'administrative' => $this->evidence_administrative,
            'work_practice' => $this->evidence_work_practice,
            'ppe' => $this->evidence_ppe,
        ];
    }
}
