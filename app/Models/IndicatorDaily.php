<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorDaily extends Model
{
    protected $table = 'indicator_daily';
    protected $fillable = ['site_id','indicator_id','date','value','note'];

    public function site()      { return $this->belongsTo(Site::class); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
}
