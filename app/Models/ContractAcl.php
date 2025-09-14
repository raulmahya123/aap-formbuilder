<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAcl extends Model
{
    protected $fillable = ['contract_id','user_id','perm'];
}
