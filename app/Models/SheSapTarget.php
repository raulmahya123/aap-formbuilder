<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheSapTarget extends Model
{
    use HasFactory;

    protected $table = 'she_sap_targets';

    protected $fillable = [
        'user_id',
        'department_id',
        'site_id',
        'year',
        'month',
        'target_inspection',
        'target_pto',
        'target_hazard',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'target_inspection' => 'integer',
        'target_pto' => 'integer',
        'target_hazard' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
