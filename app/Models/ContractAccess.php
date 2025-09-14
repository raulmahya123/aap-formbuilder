<?php

// app/Models/ContractAccess.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAccess extends Model
{
    protected $fillable = [
        'contract_id','user_id','email','status','verified_at'
    ];

    protected $casts = ['verified_at' => 'datetime'];

    public function contract() {
        return $this->belongsTo(Contract::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
