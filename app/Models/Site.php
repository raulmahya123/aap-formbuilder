<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    // Kolom yang boleh di-mass assign (WAJIB ada company_id)
    protected $fillable = [
        'code',
        'name',
        'description',
        'company_id',
    ];

    // (Opsional) casting dasar
    protected $casts = [
        'company_id' => 'integer',
    ];

    /* ======================
     *        RELASI
     * ====================== */

    // Site → Company (banyak ke satu)
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Site ↔ Users (many-to-many) via user_site_access
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_site_access')
            ->using(UserSiteAccess::class)
            ->withTimestamps();
    }

    // Site → IndicatorDaily (one-to-many)
    public function dailies()
    {
        return $this->hasMany(IndicatorDaily::class);
    }

    // Site → IndicatorValue (one-to-many)
    public function monthlies()
    {
        return $this->hasMany(IndicatorValue::class);
    }

    /* ======================
     *     MUTATORS (opsi)
     * ====================== */

    // Jaga konsistensi: code selalu UPPERCASE saat set & get
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = $value ? strtoupper($value) : $value;
    }

    public function getCodeAttribute($value): ?string
    {
        return $value ? strtoupper($value) : $value;
    }
}
