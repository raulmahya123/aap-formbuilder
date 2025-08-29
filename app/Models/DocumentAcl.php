<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAcl extends Model
{
    protected $fillable = ['document_id','user_id','department_id','perm'];

    public function document(){ return $this->belongsTo(Document::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function department(){ return $this->belongsTo(Department::class); }
}
