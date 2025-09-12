<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserSiteAccess extends Pivot
{
    // Pakai nama tabel kamu
    protected $table = 'user_site_access';

    // Kalau tabel punya kolom id autoincrement (disarankan)
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    // Kalau tabel TIDAK punya id dan hanya (user_id, site_id),
    // gunakan ini sebagai ganti 3 baris di atas:
    // public $incrementing = false;
    // protected $primaryKey = null;

    public $timestamps = true; // set true kalau tabel ada created_at/updated_at
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi bantu (opsional)
    public function user() { return $this->belongsTo(User::class); }
    public function site() { return $this->belongsTo(Site::class); }
}
