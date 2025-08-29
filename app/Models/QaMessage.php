<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class QaMessage extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
        'is_official_answer',
        'parent_id',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_official_answer' => 'boolean',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(QaThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(QaMessage::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(QaMessage::class, 'parent_id');
    }
}
