<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class QaMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
        'message_type',       // 'text', 'report', 'system'
        'is_official_answer',
        'parent_id',
        'forwarded_from_id',
        'attachments',
        'edited_at',
        'pinned_at',
        'pinned_by',
    ];

    protected $casts = [
        'attachments'        => 'array',
        'is_official_answer' => 'boolean',
        'edited_at'          => 'datetime',
        'pinned_at'          => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(QaThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forwardedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_from_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(QaMessage::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(QaMessage::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatMessageReaction::class, 'message_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ChatReport::class, 'message_id');
    }
}
