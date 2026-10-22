<?php

namespace App\Policies;

use App\Models\User;

class UserSiteAccessPolicy
{
    public function manageSiteAccess(User $user): bool
    {
        return $user->role === 'super_admin';
    }
}
