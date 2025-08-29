<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model {
    protected $fillable = ['name','header_config','footer_config','signature_config','layout_config'];
    protected $casts = [
        'header_config'=>'array',
        'footer_config'=>'array',
        'signature_config'=>'array',
        'layout_config'=>'array',
    ];
}
