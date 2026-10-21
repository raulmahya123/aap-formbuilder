<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessageReaction extends Model
{
    protected $fillable = [
        'message_id',
        'user_id',
        'reaction',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(QaMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
