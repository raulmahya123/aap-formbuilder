<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatReportStatusHistory extends Model
{
    protected $fillable = [
        'report_id',
        'user_id',
        'from_status',
        'to_status',
        'notes',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ChatReport::class, 'report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
