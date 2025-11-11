<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa di-mass assign.
     * Tambahkan company_id di sini agar insert/update ikut terisi.
     */
    protected $fillable = [
        'company_id',
        'site_id',
        'department_id',
        'created_by',
        'title',
        'slug',
        'doc_type',
        'type',        // 'builder' | 'pdf'
        'schema',      // JSON
        'pdf_path',    // nullable
        'is_active',
        'description', // opsional (pastikan kolom ada jika dipakai scopeSearch)
    ];

    /**
     * Casting kolom.
     */
    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * (Opsional) kalau kamu pakai binding {form:slug} di routes.
     * Kalau di beberapa route kamu masih pakai {form} (id), hapus method ini.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Normalisasi & pembuatan slug unik saat create/update.
     */
    protected static function booted()
    {
        // Pastikan doc_type uppercase sebelum create/update
        static::saving(function (self $form) {
            if (!empty($form->doc_type)) {
                $form->doc_type = strtoupper($form->doc_type);
            }
            if (isset($form->title)) {
                $form->title = trim((string) $form->title);
            }
        });

        // Generate slug saat creating jika kosong
        static::creating(function (self $form) {
            if (blank($form->slug)) {
                $base = Str::slug($form->title ?: 'form');
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $form->slug = $slug;
            }
        });

        // Jaga-jaga: saat updating dan slug kosong
        static::updating(function (self $form) {
            if (blank($form->slug)) {
                $base = Str::slug($form->title ?: 'form');
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

    // =========================
    // Relationships
    // =========================

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries()
    {
        return $this->hasMany(FormEntry::class);
    }

    // =========================
    // Scopes
    // =========================

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

    public function scopeForCompany($q, $companyId)
    {
        return $companyId ? $q->where('company_id', $companyId) : $q;
    }

    public function scopeForSite($q, $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeForDepartment($q, $deptId)
    {
        return $deptId ? $q->where('department_id', $deptId) : $q;
    }

    public function scopeDocType($q, ?string $docType)
    {
        $docType = strtoupper((string) $docType);
        return in_array($docType, ['SOP','IK','FORM'], true)
            ? $q->where('doc_type', $docType)
            : $q;
    }

    public function scopeType($q, ?string $type)
    {
        $type = strtolower((string) $type);
        return in_array($type, ['builder','pdf'], true)
            ? $q->where('type', $type)
            : $q;
    }
}
