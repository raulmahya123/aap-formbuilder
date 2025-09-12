<?php

namespace App\Policies;

use App\Models\Indicator;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndicatorPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
    }

    protected function isAdmin(User $user): bool
    {
        return method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function viewAny(User $user): bool { return $this->isAdmin($user); }
    public function view(User $user, Indicator $m): bool { return $this->isAdmin($user); }
    public function create(User $user): bool { return $this->isAdmin($user); }
    public function update(User $user, Indicator $m): bool { return $this->isAdmin($user); }
    public function delete(User $user, Indicator $m): bool { return $this->isAdmin($user); }
    public function restore(User $user, Indicator $m): bool { return $this->isAdmin($user); }
    public function forceDelete(User $user, Indicator $m): bool { return $this->isAdmin($user); }
}
