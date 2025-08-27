<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntryFile extends Model
{
    protected $fillable = ['form_entry_id','field_name','original_name','mime','size','path'];

    public function entry(){ return $this->belongsTo(FormEntry::class, 'form_entry_id'); }
}
