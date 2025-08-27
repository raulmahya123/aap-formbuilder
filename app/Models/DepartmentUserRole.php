<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentUserRole extends Model
{
    protected $fillable = ['department_id','user_id','dept_role'];
}
