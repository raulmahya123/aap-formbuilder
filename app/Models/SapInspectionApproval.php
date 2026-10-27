<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionApproval extends Model
{
    protected $table = 'sap_inspection_approvals';

    protected $fillable = ['inspection_id', 'actor_id', 'action', 'comments'];

    public function inspection()
    {
        return $this->belongsTo(SapInspection::class, 'inspection_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
