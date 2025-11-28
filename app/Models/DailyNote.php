<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyNote extends Model
{
    protected $table = 'daily_notes';

    protected $fillable = [
        'user_id',
        'company_id',   // tambahan
        'site_id',      // tambahan
        'title',
        'content',
        'note_time',
    ];

    protected $casts = [
        'note_time'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== Relasi =====
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
