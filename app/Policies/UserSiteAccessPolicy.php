<?php

namespace App\Policies;

use App\Models\User;

class UserSiteAccessPolicy
{
    public function manageSiteAccess(User $user): bool
    {
        // Ganti logika sesuai sistem role Anda
        return $user->is_admin ?? false;
    }
}
