<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheDocumentRevision extends Model
{
    protected $table = 'she_document_revisions';

    protected $fillable = [
        'document_id', 'revision_number', 'version',
        'file_path', 'change_summary', 'revised_by',
    ];

    public function document()
    {
        return $this->belongsTo(SheDocument::class, 'document_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
