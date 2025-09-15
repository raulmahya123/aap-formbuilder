<?php

namespace App\Policies;

use App\Models\User;

class UserSiteAccessPolicy
{
    /**
     * Hanya admin & super admin yang boleh kelola akses site.
     * Kalau isAdmin() kamu sudah memasukkan super admin, cukup return $user->isAdmin().
     */
    public function manageSiteAccess(User $user): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) return true;
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
        return false;
    }
}
