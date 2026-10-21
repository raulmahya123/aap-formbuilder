<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ChatReport extends Model
{
    protected $fillable = [
        'thread_id',
        'message_id',
        'user_id',
        'title',
        'report_type',
        'site_id',
        'department_id',
        'priority',
        'description',
        'status',
        'assigned_to',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(QaThread::class, 'thread_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(QaMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ChatReportStatusHistory::class, 'report_id')->latest();
    }
}
