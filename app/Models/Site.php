<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    public function displayLabel(): string
    {
        $code = trim((string) $this->code);
        $name = trim((string) $this->name);

        if ($code === '') return $name;
        if ($name === '') return $code;

        $containsToken = static function (string $text, string $token): bool {
            return preg_match(
                '/(^|[^\p{L}\p{N}])'.preg_quote(Str::upper($token), '/').'($|[^\p{L}\p{N}])/u',
                Str::upper($text)
            ) === 1;
        };

        // Some records include their code inside the display name (for example,
        // code "DBK" and name "AAP-DBK"). Keep the more descriptive value once.
        if ($containsToken($name, $code)) return $name;
        if ($containsToken($code, $name)) return $code;

        return $code.' — '.$name;
    }

    // Kolom yang boleh di-mass assign (WAJIB ada company_id)
    protected $fillable = [
        'code',
        'name',
        'description',
        'company_id',
        'latitude',
        'longitude',
    ];

    // (Opsional) casting dasar
    protected $casts = [
        'company_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
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
