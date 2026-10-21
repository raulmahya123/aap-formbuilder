<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany, HasOne};

class QaThread extends Model
{
    protected $fillable = [
        'subject',
        'type',             // 'personal', 'group', 'site'
        'group_type',       // 'site', 'department', 'project', 'general'
        'description',
        'avatar_path',
        'direct_hash',
        'scope',            // 'public', 'private'
        'department_id',
        'company_id',
        'site_id',
        'created_by',
        'status',           // 'open', 'resolved', 'archived'
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'company_id'      => 'integer',
        'site_id'         => 'integer',
        'department_id'   => 'integer',
    ];

    /** Pembuat thread */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** Pesan dalam thread */
    public function messages(): HasMany
    {
        return $this->hasMany(QaMessage::class, 'thread_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(QaMessage::class, 'thread_id')->latestOfMany();
    }

    /** Peserta chat */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'qa_participants', 'thread_id', 'user_id')
            ->withPivot(['role_label', 'chat_role', 'is_muted', 'is_pinned', 'pinned_at', 'is_archived', 'last_read_at'])
            ->withTimestamps();
    }

    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(QaMessage::class, 'thread_id')->whereNotNull('pinned_at')->orderByDesc('pinned_at');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ChatReport::class, 'thread_id');
    }

    /** Helper untuk generate direct_hash 1-1 */
    public static function makeDirectHash(int $userA, int $userB): string
    {
        $min = min($userA, $userB);
        $max = max($userA, $userB);
        return hash('sha256', "direct_{$min}_{$max}");
    }
}
