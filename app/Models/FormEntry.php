<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'user_id',
        'data',             // JSON jawaban user
        'pdf_output_path',  // path file PDF (opsional)
    ];

    protected $casts = [
        'data' => 'array',  // otomatis JSON <-> array
    ];

    // === Accessor/Alias ===
    public function getAnswersAttribute(): array
    {
        // supaya $entry->answers bisa dipakai di view
        return $this->data ?? [];
    }

    // === RELASI ===
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(FormEntryFile::class);
    }

    public function approvals()
    {
        return $this->hasMany(FormEntryApproval::class);
    }
}
