<?php

namespace App\Policies;

use App\Models\IndicatorValue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndicatorValuePolicy
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

    protected function hasSite(User $user, ?int $siteId): bool
    {
        if (!$siteId) return false;
        if ($this->isAdmin($user)) return true;

        if (method_exists($user, 'hasSite')) {
            return $user->hasSite($siteId);
        }
        if (method_exists($user, 'sites')) {
            return $user->sites()->where('site_id', $siteId)->exists();
        }
        return false;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || (method_exists($user,'sites') && $user->sites()->exists());
    }

    public function view(User $user, IndicatorValue $row): bool
    {
        return $this->hasSite($user, (int) $row->site_id);
    }

    public function create(User $user): bool { return $this->isAdmin($user); }
    public function update(User $user, IndicatorValue $row): bool { return $this->isAdmin($user); }
    public function delete(User $user, IndicatorValue $row): bool { return $this->isAdmin($user); }
    public function restore(User $user, IndicatorValue $row): bool { return $this->isAdmin($user); }
    public function forceDelete(User $user, IndicatorValue $row): bool { return $this->isAdmin($user); }
}
