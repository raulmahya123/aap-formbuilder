<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionSchedule extends Model
{
    protected $table = 'sap_inspection_schedules';

    protected $fillable = [
        'template_id', 'site_id', 'department_id', 'assigned_to',
        'due_date', 'period', 'year', 'month', 'status', 'created_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(SapInspectionTemplate::class, 'template_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspections()
    {
        return $this->hasMany(SapInspection::class, 'schedule_id');
    }
}
