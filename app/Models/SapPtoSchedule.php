<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapPtoSchedule extends Model
{
    protected $table = 'sap_pto_schedules';

    protected $fillable = [
        'template_id',
        'site_id',
        'department_id',
        'observer_id',
        'due_date',
        'period',
        'year',
        'month',
        'status',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
