<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionLog extends Model
{
    protected $table = 'sap_inspection_logs';

    protected $fillable = ['inspection_id', 'user_id', 'action', 'description', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function inspection()
    {
        return $this->belongsTo(SapInspection::class, 'inspection_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
