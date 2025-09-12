<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorValue extends Model
{
    protected $fillable = ['site_id','indicator_id','year','month','value'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }
}
