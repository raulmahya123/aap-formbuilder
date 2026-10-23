<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseTrainingCourse extends Model
{
    protected $table = 'hse_training_courses';

    protected $fillable = ['code', 'name', 'category', 'is_mandatory', 'validity_months', 'is_active'];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'validity_months' => 'integer',
    ];

    public function records()
    {
        return $this->hasMany(HseTrainingRecord::class, 'course_id');
    }
}
