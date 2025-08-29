<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class QaThread extends Model
{
    protected $fillable = [
        'subject',
        'scope',
        'department_id',
        'created_by',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /** Pembuat thread */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Pesan dalam thread */
    public function messages(): HasMany
    {
        return $this->hasMany(QaMessage::class, 'thread_id');
    }

    /** Peserta (untuk thread private) */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'qa_participants', 'thread_id', 'user_id')
            ->withPivot(['role_label','is_muted','last_read_at'])
            ->withTimestamps();
    }
}
