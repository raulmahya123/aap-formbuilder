<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    protected $fillable = ['name','slug'];

    protected static function booted() {
        static::saving(function(self $m){
            if (!$m->slug) $m->slug = Str::slug($m->name);
        });
    }

    public function users(){ return $this->belongsToMany(User::class, 'department_user_roles')->withPivot('dept_role')->withTimestamps(); }
    public function forms(){ return $this->hasMany(Form::class); }
}
