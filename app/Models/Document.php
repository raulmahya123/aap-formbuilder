<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = [
        'template_id','doc_no','dept_code','doc_type','project_code','revision_no',
        'effective_date','title','controlled_status','class',
        'header_config','footer_config','signature_config','sections','meta',
        'owner_id','department_id'
    ];
    protected $casts = [
        'effective_date'=>'date',
        'header_config'=>'array',
        'footer_config'=>'array',
        'signature_config'=>'array',
        'sections'=>'array',
        'meta'=>'array',
    ];
    // app/Models/Document.php
public function acls()
{
    return $this->hasMany(\App\Models\DocumentAcl::class);
}


    public function template(){ return $this->belongsTo(DocumentTemplate::class); }
    public function signatures(){ return $this->hasMany(DocumentSignature::class)->orderBy('order'); }
    public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
    public function department(){ return $this->belongsTo(Department::class); }
}
