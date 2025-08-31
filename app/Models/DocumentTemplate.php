<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// app/Models/DocumentTemplate.php
class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'blocks_config',
        'layout_config',
        'header_config',
        'footer_config',
        'signature_config',
    ];

    protected $casts = [
        'blocks_config'   => 'array',
        'layout_config'   => 'array',
        'header_config'   => 'array',
        'footer_config'   => 'array',
        'signature_config'=> 'array',
    ];
}
