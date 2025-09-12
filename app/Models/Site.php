<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = ['name','code','description'];

    public function users()    { return $this->belongsToMany(User::class, 'user_site_access'); }
    public function dailies()  { return $this->hasMany(IndicatorDaily::class); }
    public function monthlies(){ return $this->hasMany(IndicatorValue::class); }
}
