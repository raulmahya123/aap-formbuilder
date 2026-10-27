<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapPtoFinding extends Model
{
    use SoftDeletes;

    protected $table = 'sap_pto_findings';

    protected $fillable = [
        'finding_no',
        'pto_id',
        'answer_id',
        'item_id',
        'site_id',
        'department_id',
        'title',
        'description',
        'risk_class',
        'pic_id',
        'due_date',
        'status',
        'closure_notes',
        'verified_by',
        'verified_at',
        'closed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'verified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function pto(): BelongsTo
    {
        return $this->belongsTo(SapPtoRecord::class, 'pto_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(SapPtoAnswer::class, 'answer_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SapPtoChecklistItem::class, 'item_id');
    }
}
