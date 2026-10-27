<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapHazardTemporaryAction extends Model
{
    protected $table = 'sap_hazard_temporary_actions';

    protected $fillable = [
        'hazard_id',
        'description',
        'action_date',
        'pic_id',
        'status',
        'remarks',
        'performed_by',
    ];

    protected $casts = [
        'action_date' => 'date',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(SapHazardReport::class, 'hazard_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
