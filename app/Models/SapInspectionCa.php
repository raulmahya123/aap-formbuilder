<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionCa extends Model
{
    protected $table = 'sap_inspection_cas';

    protected $fillable = [
        'finding_id', 'inspection_id', 'action_by', 'corrective_action',
        'preventive_action', 'target_completion_date', 'actual_completion_date',
        'status', 'evidence_notes', 'verified_by', 'verified_at', 'verification_notes'
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function finding()
    {
        return $this->belongsTo(SapInspectionFinding::class, 'finding_id');
    }

    public function inspection()
    {
        return $this->belongsTo(SapInspection::class, 'inspection_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function attachments()
    {
        return $this->morphMany(SapInspectionAttachment::class, 'attachable');
    }
}
