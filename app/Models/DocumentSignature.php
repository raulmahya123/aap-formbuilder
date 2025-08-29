<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model {
    protected $fillable = [
        'document_id',
        'role',
        'name',
        'position_title',
        'image_path',
        'order',
        // kolom e-sign tambahan
        'signed_by_user_id',
        'signed_at',
        'signed_image_path',
        'signed_hash',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function document() {
        return $this->belongsTo(Document::class);
    }

    public function signedBy() {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
