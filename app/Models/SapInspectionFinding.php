<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapInspectionFinding extends Model
{
    use SoftDeletes;

    protected $table = 'sap_inspection_findings';

    protected $fillable = [
        'finding_no', 'inspection_id', 'answer_id', 'item_id', 'site_id', 'department_id',
        'title', 'description', 'risk_class', 'pic_id', 'due_date', 'status',
        'closure_notes', 'verified_by', 'verified_at', 'closed_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'verified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function inspection()
    {
        return $this->belongsTo(SapInspection::class, 'inspection_id');
    }

    public function answer()
    {
        return $this->belongsTo(SapInspectionAnswer::class, 'answer_id');
    }

    public function item()
    {
        return $this->belongsTo(SapInspectionItem::class, 'item_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function cas()
    {
        return $this->hasMany(SapInspectionCa::class, 'finding_id');
    }

    public function attachments()
    {
        return $this->morphMany(SapInspectionAttachment::class, 'attachable');
    }
}
