<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorGroup extends Model
{
    protected $fillable = ['name','code','order_index','is_active'];

    public function indicators() {
        return $this->hasMany(Indicator::class)->orderBy('order_index');
    }
}
