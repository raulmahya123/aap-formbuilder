<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $fillable = ['owner_id','title','file_path','size_bytes','mime','uuid'];

    protected static function booted() {
        static::creating(fn($c) => $c->uuid = $c->uuid ?: (string) Str::uuid());
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function viewers(): BelongsToMany {
        return $this->belongsToMany(User::class,'contract_acls')
            ->withTimestamps()->withPivot('perm');
    }
}
