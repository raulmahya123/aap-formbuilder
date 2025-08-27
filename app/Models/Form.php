<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'department_id','created_by','title','slug','type','schema','pdf_path','is_active',
    ];

    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (self $form) {
            // Ambil dasar slug: prioritas slug dari request, kalau kosong pakai title
            $base = Str::slug($form->slug ?: $form->title);
            $slug = $base;
            $i = 1;

            // Selalu cek bentrokan (global)
            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }

            $form->slug = $slug;
        });

        static::updating(function (self $form) {
            // Kalau slug dikosongkan, regenerate unik dari title
            if (blank($form->slug)) {
                $base = Str::slug($form->title);
                $slug = $base;
                $i = 1;

                while (static::where('slug', $slug)->where('id', '!=', $form->id)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $form->slug = $slug;
            }
        });
    }

    public function department(){ return $this->belongsTo(Department::class); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function entries(){ return $this->hasMany(FormEntry::class); }
}
