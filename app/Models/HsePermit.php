<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsePermit extends Model
{
    use SoftDeletes;

    protected $table = 'hse_permits';

    protected $fillable = [
        'permit_no', 'site_id', 'department_id', 'type', 'issued_to',
        'valid_from', 'valid_to', 'status', 'notes',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public const STATUSES = ['Active', 'Expired', 'Pending', 'Revoked'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }
}
