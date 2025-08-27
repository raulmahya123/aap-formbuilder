<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntry extends Model
{
    protected $fillable = [
        'form_id',
        'user_id',
        'data',              // JSON
        'pdf_output_path',   // path file bukti PDF (opsional)
    ];

    protected $casts = [
        'data' => 'array',   // supaya $entry->data jadi array
    ];

    // ========== RELASI ==========
    public function form()
    {
        // pastikan model Form ada di App\Models\Form
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function user()
    {
        // pastikan User ada di App\Models\User
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files()
    {
        // tabel default: form_entry_files, FK: form_entry_id
        return $this->hasMany(FormEntryFile::class, 'form_entry_id');
    }

    public function approvals()
    {
        // kalau kamu pakai tabel approval
        return $this->hasMany(FormEntryApproval::class, 'form_entry_id');
    }
}
