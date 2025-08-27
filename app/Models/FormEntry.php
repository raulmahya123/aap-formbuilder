<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntry extends Model
{
    protected $fillable = ['form_id','user_id','data','pdf_output_path'];
    protected $casts = ['data'=>'array'];

    public function form(){ return $this->belongsTo(Form::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function approvals(){ return $this->hasMany(\App\Models\FormEntryApproval::class, 'form_entry_id'); }

}
