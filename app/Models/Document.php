<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'template_id',
        'title','dept_code','doc_type','project_code','revision_no',
        'effective_date','controlled_status','class','department_id',
        'doc_no','owner_id',
        'layout_config','header_config','footer_config','signature_config','sections',
    ];

    protected $casts = [
        'effective_date'   => 'date',
        'layout_config'    => 'array',
        'header_config'    => 'array',
        'footer_config'    => 'array',
        'signature_config' => 'array',
        'sections'         => 'array',
    ];

    // Relasi
     public function bumpRevision(): void
    {
        $this->revision_no = (int)($this->revision_no ?? 0) + 1;
    }
    public function template(): BelongsTo { return $this->belongsTo(DocumentTemplate::class, 'template_id'); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function signatures(): HasMany { return $this->hasMany(DocumentSignature::class); }
    public function acls(): HasMany { return $this->hasMany(DocumentAcl::class); }
}
