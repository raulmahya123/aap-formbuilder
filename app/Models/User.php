<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_active'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /* =========================
     | Roles / Depts
     * ========================= */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user_roles')
            ->withPivot('dept_role')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin()
            || $this->departments()->wherePivot('dept_role', 'dept_admin')->exists();
    }

    public function isDeptAdminOf(int $departmentId): bool
    {
        return $this->departments()
            ->where('department_id', $departmentId)
            ->wherePivot('dept_role', 'dept_admin')
            ->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* =========================
     | Sites
     * ========================= */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'user_site_access')
            ->using(UserSiteAccess::class)
            ->withTimestamps();
    }

    public function canAccessSite(int $siteId): bool
    {
        return $this->sites()->where('sites.id', $siteId)->exists();
    }

    /* =========================
     | Contracts
     * ========================= */
    // PENTING: pakai owner_id sebagai FK (BUKAN user_id)
    public function ownedContracts()
    {
        return $this->hasMany(Contract::class, 'owner_id');
    }

    // Kontrak yang dibagikan ke user via ACL
    public function sharedContracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_acls')
            ->withPivot('perm')
            ->withTimestamps();
    }
}
