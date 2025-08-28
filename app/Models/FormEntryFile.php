<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntryFile extends Model
{
    protected $table = 'form_entry_files';

    protected $fillable = [
        'form_entry_id',
        'field_name',     // <- WAJIB: dipakai di controller & kolom DB
        'original_name',  // <- WAJIB: dipakai di controller
        'mime',
        'size',
        'path',
    ];

    public function entry()
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }
}
