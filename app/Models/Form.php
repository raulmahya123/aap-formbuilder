<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'department_id',
        'site_id',        // ⬅️ ditambahkan
        'created_by',
        'title',
        'slug',
        'type',
        'schema',
        'pdf_path',
        'is_active',
    ];

    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'boolean',
    ];

    // Pakai slug untuk route model binding: {form:slug}
    public function getRouteKeyName()
    {
        return 'slug';
    }

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

    // ===== Relasi =====
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function site()
    {
        // default FK: site_id
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries()
    {
        return $this->hasMany(FormEntry::class);
    }

    // ===== Scopes bantu (opsional tapi praktis) =====
    public function scopeActive($q, bool $onlyActive = true)
    {
        return $onlyActive ? $q->where('is_active', true) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeForSite($q, $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeForDepartment($q, $deptId)
    {
        return $deptId ? $q->where('department_id', $deptId) : $q;
    }
}
