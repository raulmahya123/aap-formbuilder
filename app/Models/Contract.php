<?php

// app/Models/Contract.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $fillable = [
        'title','slug','description','images',
        'visibility','site_id','created_by','expires_at'
    ];

    protected $casts = [
        'images'     => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function booted() {
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title.'-'.Str::random(6));
            }
        });
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function site() {
        return $this->belongsTo(Site::class);
    }

    public function accesses() {
        return $this->hasMany(ContractAccess::class);
    }
}
