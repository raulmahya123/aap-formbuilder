<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'department_id',
        'site_id',
        'created_by',
        'title',
        'slug',
        'doc_type',   // <-- WAJIB: biar mass-assignment mengisi doc_type
        'type',
        'schema',
        'pdf_path',
        'is_active',
        'description', // kalau kolom ini ada di tabel (sesuai scopeSearch)
    ];

    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'boolean',
    ];

    // (Opsional tapi bagus) normalisasi ke uppercase saat set
    public function setDocTypeAttribute($value): void
    {
        $this->attributes['doc_type'] = $value ? strtoupper($value) : null;
    }

    // Pakai slug untuk route model binding: {form:slug}
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function (self $form) {
            // slug dari slug (jika sudah ada) atau title
            $base = Str::slug($form->slug ?: $form->title);
            $slug = $base;
            $i = 1;
            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $form->slug = $slug;
        });

        static::updating(function (self $form) {
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

    // ===== Scopes =====
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
                ->orWhere('description', 'like', "%{$term}%"); // pastikan kolom description ada
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

    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
