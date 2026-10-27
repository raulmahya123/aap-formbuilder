<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapInspectionAttachment extends Model
{
    protected $table = 'sap_inspection_attachments';

    protected $fillable = [
        'attachable_type', 'attachable_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'uploaded_by'
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
