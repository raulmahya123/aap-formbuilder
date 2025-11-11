<?php

namespace App\Models;

use App\Models\User;
use App\Models\Form;
use App\Models\Site;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    // ====== Table & Primary (opsional, default sudah 'companies' & 'id') ======
    protected $table = 'companies';

    // ====== Mass assignment ======
    protected $fillable = [
        'code', 'name', 'legal_name', 'slug', 'industry',
        'registration_no', 'npwp', 'nib',
        'email', 'phone', 'website',
        'hq_address', 'city', 'province', 'postal_code', 'country',
        'addresses',
        'timezone', 'currency',
        'logo_path',
        'status',
        'created_by', 'updated_by',
    ];

    // ====== Casts ======
    protected $casts = [
        'addresses'  => 'array',   // simpan/ambil sebagai array
        'deleted_at' => 'datetime',
    ];

    // Otomatis ikut saat toArray()/JSON
    protected $appends = [
        'logo_url',
    ];

    // ====== Scopes ======
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    // ====== Accessors / Mutators ======

    /**
     * Selalu simpan & ambil code sebagai UPPERCASE.
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn ($v) => $v ? strtoupper($v) : $v,
            set: fn ($v) => $v ? strtoupper($v) : $v,
        );
    }

    /**
     * Jika slug dikosongkan saat set, auto-generate dari name/code dan pastikan unik.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: function ($v, array $attr) {
                if ($v === null || $v === '') {
                    $base = Str::slug($attr['name'] ?? $attr['code'] ?? 'company');
                    $slug = $base;
                    $i = 1;
                    while (static::where('slug', $slug)->exists()) {
                        $slug = $base . '-' . $i++;
                    }
                    return $slug;
                }
                return $v;
            }
        );
    }

    /**
     * URL logo siap pakai di Blade.
     * Meng-handle kasus logo_path yang tersimpan dengan prefix 'public/' atau 'storage/'.
     * Hanya mengembalikan URL jika file benar-benar ada.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $p = $this->logo_path ?? null;
        if (!$p) {
            return null;
        }

        // Normalisasi prefix salah
        $p = ltrim(preg_replace('#^(public/|storage/)#', '', $p), '/');

        // Pastikan filenya exist di disk 'public'
        if (Storage::disk('public')->exists($p)) {
            // gunakan asset('storage/...') agar aman di dev/prod
            return asset('storage/' . $p);
        }

        return null;
    }

    // ====== Relasi ======

    // Pembuat & pengubah (opsional, jika punya tabel users)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Forms milik perusahaan (butuh kolom company_id di forms)
    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    // Sites milik perusahaan (butuh kolom company_id di sites)
    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    // Departments milik perusahaan (butuh kolom company_id di departments)
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    // Users milik perusahaan (jika multi-tenant user→company)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
