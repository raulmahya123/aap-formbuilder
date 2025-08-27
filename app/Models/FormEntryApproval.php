<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntryApproval extends Model
{
    protected $fillable = ['form_entry_id','actor_id','action','notes'];

    public function entry(){ return $this->belongsTo(FormEntry::class, 'form_entry_id'); }
    public function actor(){ return $this->belongsTo(User::class, 'actor_id'); }
}
