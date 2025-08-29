<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DocumentAcl extends Model {
    protected $table = 'document_acl';
    protected $fillable = ['document_id','user_id','department_id','perm'];
}
