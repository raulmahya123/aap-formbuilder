<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh di-mass assign.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active', // ← tambahkan ini
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean', // ← tambahkan ini
        ];
    }

    // ================== RELASI & HELPER ==================

    public function departments()
    {
        return $this->belongsToMany(\App\Models\Department::class, 'department_user_roles')
            ->withPivot('dept_role')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDeptAdminOf(int $departmentId): bool
    {
        return $this->departments()
            ->where('department_id', $departmentId)
            ->wherePivot('dept_role', 'dept_admin')
            ->exists();
    }

    /**
     * Scope bantu: hanya user aktif.
     * Contoh: User::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
