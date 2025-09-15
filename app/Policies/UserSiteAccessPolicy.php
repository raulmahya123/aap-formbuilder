<?php

namespace App\Policies;

use App\Models\User;

class UserSiteAccessPolicy
{
    /**
     * Admin, super admin, dan user biasa boleh kelola akses site.
     */
    public function manageSiteAccess(User $user): bool
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) return true;
        if (method_exists($user, 'isUser') && $user->isUser()) return true;

        return false;
    }
}
