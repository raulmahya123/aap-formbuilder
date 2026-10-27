<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapInspection extends Model
{
    use SoftDeletes;

    protected $table = 'sap_inspections';

    protected $fillable = [
        'inspection_no', 'template_id', 'schedule_id', 'site_id', 'department_id',
        'inspector_id', 'auditor_id', 'location', 'started_at', 'completed_at',
        'submitted_at', 'score', 'max_score', 'percentage', 'status', 'notes',
        'review_notes', 'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'score' => 'float',
        'max_score' => 'float',
        'percentage' => 'float',
    ];

    public function template()
    {
        return $this->belongsTo(SapInspectionTemplate::class, 'template_id');
    }

    public function schedule()
    {
        return $this->belongsTo(SapInspectionSchedule::class, 'schedule_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function answers()
    {
        return $this->hasMany(SapInspectionAnswer::class, 'inspection_id');
    }

    public function findings()
    {
        return $this->hasMany(SapInspectionFinding::class, 'inspection_id');
    }

    public function attachments()
    {
        return $this->morphMany(SapInspectionAttachment::class, 'attachable');
    }

    public function approvals()
    {
        return $this->hasMany(SapInspectionApproval::class, 'inspection_id')->orderBy('created_at');
    }

    public function logs()
    {
        return $this->hasMany(SapInspectionLog::class, 'inspection_id')->orderBy('created_at');
    }
}
