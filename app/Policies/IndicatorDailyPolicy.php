<?php

namespace App\Policies;

use App\Models\IndicatorDaily;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndicatorDailyPolicy
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

    // List: admin atau user yang punya minimal 1 site (filter data tetap di controller)
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || (method_exists($user,'sites') && $user->sites()->exists());
    }

    public function view(User $user, IndicatorDaily $row): bool
    {
        return $this->hasSite($user, (int) $row->site_id);
    }

    // Standar create() → admin saja; untuk per-site gunakan createForSite
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Custom ability: authorize create untuk site tertentu.
     * Pakai di controller:
     *   $this->authorize('createForSite', [IndicatorDaily::class, $siteId]);
     */
    public function createForSite(User $user, $site): bool
    {
        $siteId = is_numeric($site) ? (int) $site : ($site->id ?? null);
        return $this->hasSite($user, $siteId);
    }

    public function update(User $user, IndicatorDaily $row): bool
    {
        return $this->hasSite($user, (int) $row->site_id);
    }

    public function delete(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }
}
