<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntryFile extends Model
{
    protected $fillable = [
        'form_entry_id',
        'path',      // path di storage (public)
        'name',      // nama file asli
        'size',      // ukuran (byte) - opsional
        'mime',      // mime type - opsional
    ];

    public function entry()
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }
}
