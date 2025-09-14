<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'file_path',
        'size_bytes',
        'mime',
        'uuid',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['size_kb'];

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            $c->uuid ??= (string) Str::uuid();
            $c->mime ??= 'application/pdf';
        });
    }

    /* Relationships */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contract_acls')
            ->withPivot('perm')
            ->withTimestamps();
    }

    /* Scopes */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($w) use ($user) {
            $w->where('owner_id', $user->id)
                ->orWhereIn('id', function ($sub) use ($user) {
                    $sub->select('contract_id')
                        ->from('contract_acls')
                        ->where('user_id', $user->id);
                });
        });
    }

    public function scopeSearch($query, ?string $q)
    {
        return $q ? $query->where('title', 'like', '%' . trim($q) . '%') : $query;
        // NB: jangan bikin where user_id di sini
    }

    /* Accessors / Helpers */
    public function getSizeKbAttribute(): ?string
    {
        return is_null($this->size_bytes) ? null : number_format($this->size_bytes / 1024, 1);
    }

    public function storageDisk(): string
    {
        return 'private';
    }

    public function fileExists(): bool
    {
        return $this->file_path && Storage::disk($this->storageDisk())->exists($this->file_path);
    }

    public function acls()
    {
        return $this->hasMany(\App\Models\ContractAcl::class, 'contract_id');
    }


    public function deleteWithFile(): bool
    {
        if ($this->fileExists()) {
            Storage::disk($this->storageDisk())->delete($this->file_path);
        }
        if (method_exists($this, 'viewers')) {
            $this->viewers()->detach();
        }
        return (bool) $this->delete();
    }
}
