<?php

namespace App\Policies;

use App\Models\QaThread;
use App\Models\User;

class QaThreadPolicy
{
    /**
     * Boleh melihat daftar thread?
     */
    public function viewAny(User $user): bool
    {
        return (bool) ($user->is_active ?? true);
    }

    /**
     * Boleh melihat thread tertentu?
     * - public: semua user login
     * - private: hanya peserta atau super admin
     */
    public function view(User $user, QaThread $thread): bool
    {
        if ($thread->scope === 'public') {
            return true;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // private: cek apakah user peserta thread
        return $thread->participants()->where('users.id', $user->id)->exists();
    }

    /**
     * Boleh membuat thread?
     */
    public function create(User $user): bool
    {
        return (bool) ($user->is_active ?? true);
    }

    /**
     * Boleh membalas dalam thread?
     * - public: semua user login
     * - private: hanya peserta
     */
    public function reply(User $user, QaThread $thread): bool
    {
        if ($thread->scope === 'public') {
            return (bool) ($user->is_active ?? true);
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return $thread->participants()->where('users.id', $user->id)->exists();
    }

    /**
     * Boleh menandai selesai / resolve?
     * - admin atau super admin
     */
    public function resolve(User $user, QaThread $thread): bool
    {
        return (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());
    }
}
