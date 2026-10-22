<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'is_active',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'phone',
        'whatsapp',
        'address',
        'province',
        'city',
        'district',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'role',
        'position',
        'division',
        'department_id',
        'joined_date',
        'employee_status',
        'avatar_path',
    ];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'feature_access'    => 'array',
            'birth_date'        => 'date',
            'joined_date'       => 'date',
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
        return in_array($this->role, ['super_admin', 'admin_ho'], true);
    }

    public function isSiteAdmin(): bool
    {
        return $this->role === 'admin_site';
    }

    public function roleLabel(): string
    {
        return match ($this->role) { 'super_admin'=>'Super Admin', 'admin_ho'=>'Admin HO', 'admin_site'=>'Admin Site', default=>'User' };
    }

    public function chatRoleLabel(): string
    {
        return $this->isSuperAdmin() ? 'super_admin' : ($this->isSiteAdmin() ? 'admin' : 'user');
    }

    public function canUseFeature(string $feature): bool
    {
        return $this->isSuperAdmin() || $this->feature_access === null || in_array($feature, $this->feature_access, true);
    }

    public function canUseFeatureRoute(?string $route): bool
    {
        $feature = \App\Support\FeatureAccess::forRoute($route);
        return $feature === null || $this->canUseFeature($feature);
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isSiteAdmin();
    }

    public function isUser(): bool
    {
        return ($this->role ?? 'user') === 'user';
    }

    public function departmentIds(): array
    {
        return $this->departments()->pluck('departments.id')->merge(
            $this->department_id ? [$this->department_id] : []
        )->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    public function hasSiteAccess(?int $siteId): bool
    {
        if (!$siteId) return false;
        if ($this->isSuperAdmin()) return true;

        return $this->sites()->whereKey($siteId)->exists();
    }

    /** Null means HO can see every site; other roles receive only assigned sites. */
    public function allowedSiteIds(): ?array
    {
        if ($this->isSuperAdmin()) return null;

        return $this->sites()->pluck('sites.id')->map(fn ($id) => (int) $id)->all();
    }

    public function isDeptAdminOf(int $departmentId): bool
    {
        if ($this->isSiteAdmin()) return in_array($departmentId, $this->departmentIds(), true);
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
