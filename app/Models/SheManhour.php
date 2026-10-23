<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SheManhour extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'she_manhours';

    protected $fillable = [
        'date',
        'site_id',
        'company_id',
        'department_id',
        'employee_count',
        'manhours',
        'lost_hours',
        'status', // Draft, Submitted, Validated, Returned
        'notes',
        'submitted_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'validated_at' => 'datetime',
        'manhours' => 'float',
        'lost_hours' => 'float',
        'employee_count' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
