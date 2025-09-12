<?php

namespace App\Policies;

use App\Models\IndicatorDaily;
use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndicatorDailyPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin selalu lolos semua ability.
     * Jalankan sebelum ability spesifik.
     */
    public function before(User $user, string $ability)
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        // return null untuk lanjut ke ability spesifik
        return null;
    }

    /**
     * Helper: cek admin.
     */
    protected function isAdmin(User $user): bool
    {
        return method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    /**
     * Helper: cek apakah user punya akses ke site tertentu.
     * - Admin otomatis true
     * - Kalau User punya method hasSite($siteId) gunakan itu
     * - Fallback: cek relasi belongsToMany('sites') pada pivot user_site_access
     */
    protected function hasSite(User $user, ?int $siteId): bool
    {
        if (!$siteId) return false;
        if ($this->isAdmin($user)) return true;

        if (method_exists($user, 'hasSite')) {
            return (bool) $user->hasSite($siteId);
        }

        if (method_exists($user, 'sites')) {
            // relasi many-to-many: users ↔ sites lewat tabel user_site_access
            return $user->sites()->where('sites.id', $siteId)->exists();
        }

        return false;
    }

    /**
     * viewAny:
     * - Admin: boleh
     * - Non-admin: boleh jika punya minimal 1 site
     * (Filter data tetap ditangani di controller/query)
     */
    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) return true;

        return method_exists($user, 'sites') && $user->sites()->exists();
    }

    /**
     * view:
     * - Boleh jika user punya akses ke site dari row terkait.
     */
    public function view(User $user, IndicatorDaily $row): bool
    {
        return $this->hasSite($user, (int) $row->site_id);
    }

    /**
     * create (standar):
     * - Admin saja.
     * Gunakan ability kustom createForSite() untuk kontrol per site.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Ability kustom:
     * - createForSite(User $user, int|Site $site)
     * - Pakai di controller:
     *     $this->authorize('createForSite', [IndicatorDaily::class, $siteId]);
     */
    public function createForSite(User $user, $site): bool
    {
        $siteId = is_numeric($site) ? (int) $site : ($site->id ?? null);
        return $this->hasSite($user, $siteId);
    }

    /**
     * update:
     * - Boleh jika user punya akses ke site row terkait.
     */
    public function update(User $user, IndicatorDaily $row): bool
    {
        return $this->hasSite($user, (int) $row->site_id);
    }

    /**
     * delete:
     * - Admin saja.
     */
    public function delete(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * restore:
     * - Admin saja (jika soft deletes diterapkan).
     */
    public function restore(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * forceDelete:
     * - Admin saja (jika soft deletes diterapkan).
     */
    public function forceDelete(User $user, IndicatorDaily $row): bool
    {
        return $this->isAdmin($user);
    }
}
