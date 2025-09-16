<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\ShiftWindow;

class IndicatorDaily extends Model
{
    protected $table = 'indicator_daily';

    protected $fillable = [
        'site_id','indicator_id','date','value','note',
        'shift','input_at','is_late',
    ];

    protected $casts = [
        'date'     => 'date',
        'input_at' => 'datetime',
        'is_late'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $info = ShiftWindow::detect($m->date);
            $m->shift    ??= ($info['shift'] ?? $info['closest_shift']); // << fallback agar KEISI
            $m->input_at ??= $info['now'];
            $m->is_late  ??= $info['is_late'];
        });
    }

    public function site()      { return $this->belongsTo(Site::class); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
}
