<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $fillable = [
        'indicator_group_id','name','code','data_type','agg',
        'unit','order_index','is_derived','formula','is_active','threshold'
    ];

    public function group()  { return $this->belongsTo(IndicatorGroup::class, 'indicator_group_id'); }
    public function dailies(){ return $this->hasMany(IndicatorDaily::class); }
    public function monthlies(){ return $this->hasMany(IndicatorValue::class); }
}
