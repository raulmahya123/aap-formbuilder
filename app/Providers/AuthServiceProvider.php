<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// ========= MODELS (existing) =========
use App\Models\Form;
use App\Models\Department;
use App\Models\FormEntry;
use App\Models\QaThread;
use App\Models\Document;
use App\Models\DocumentTemplate; // opsional

// ========= MODELS (baru untuk fitur SITE & indikator) =========
use App\Models\Site;
use App\Models\IndicatorGroup;
use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorValue;

// ========= POLICIES (existing) =========
use App\Policies\FormPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\FormEntryPolicy;
use App\Policies\QaThreadPolicy;
use App\Policies\DocumentPolicy;
// use App\Policies\DocumentTemplatePolicy; // opsional

// ========= POLICIES (baru untuk fitur SITE & indikator) =========
use App\Policies\SitePolicy;
use App\Policies\IndicatorGroupPolicy;
use App\Policies\IndicatorPolicy;
use App\Policies\IndicatorDailyPolicy;
use App\Policies\IndicatorValuePolicy;
// use App\Policies\UserSiteAccessPolicy; // HAPUS dari mapping kalau tidak ada model-nya
use App\Models\Contract;
use App\Policies\ContractPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Map model ke policy.
     */
    protected $policies = [
        // ==== existing ====
        Form::class         => FormPolicy::class,
        Department::class   => DepartmentPolicy::class,
        FormEntry::class    => FormEntryPolicy::class,
        QaThread::class     => QaThreadPolicy::class,
        Document::class     => DocumentPolicy::class,
        // DocumentTemplate::class => DocumentTemplatePolicy::class, // opsional

        // ==== baru (SITE & indikator) ====
        Site::class             => SitePolicy::class,
        IndicatorGroup::class   => IndicatorGroupPolicy::class,
        Indicator::class        => IndicatorPolicy::class,
        IndicatorDaily::class   => IndicatorDailyPolicy::class,
        IndicatorValue::class   => IndicatorValuePolicy::class,

        // ⚠️ JANGAN pakai policy class sebagai key.
        // UserSiteAccessPolicy::class => UserSiteAccessPolicy::class, // <-- HAPUS
        Contract::class         => ContractPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // === Super Admin: auto-allow semua ability ===
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });

        // === Gate existing: approval entry (opsional) ===
        Gate::define('entry-approve', function ($user, FormEntry $entry) {
            // Contoh: super admin atau admin departemen yang bersangkutan
            return (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || (method_exists($user, 'isDeptAdminOf') && $user->isDeptAdminOf($entry->form->department_id));
        });

        // === Gate helper: admin sederhana ===
        Gate::define('is-admin', function ($user) {
            return method_exists($user, 'isAdmin') && $user->isAdmin();
        });

        // === Gate akses ke Site tertentu (admin selalu lolos) ===
        // Parameter $site bisa berupa instance Site atau ID numerik.
        Gate::define('site-access', function ($user, $site) {
            $siteId = is_numeric($site) ? (int) $site : ($site->id ?? null);
            if (!$siteId) return false;

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }

            // Pastikan User punya relasi: belongsToMany(Site::class, 'user_site_access')
            if (method_exists($user, 'sites')) {
                return $user->sites()->where('site_id', $siteId)->exists();
            }

            return false;
        });

        // === Gate baru: DAILY MANAGE (dipakai oleh StoreDailyRequest) ===
        // Izinkan admin dan siapa pun yang punya akses ke site (via 'site-access').
        Gate::define('daily.manage', function ($user, $site = null) {
            // Admin langsung lolos
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }

            // Jika dipanggil tanpa konteks site tertentu, batasi ke admin saja
            if ($site === null) {
                return false;
            }

            // Jika ada konteks site, reuse rule site-access
            return Gate::forUser($user)->allows('site-access', $site);
        });

        // === Gate lama: daily-input (boleh dipakai untuk blade/menu)
        Gate::define('daily-input', function ($user, $site) {
            return Gate::forUser($user)->allows('site-access', $site);
        });

        // === Gate upload kontrak ===
        Gate::define('contract-upload', function ($user) {
            return method_exists($user, 'isAdmin') && $user->isAdmin();
            // atau kalau semua user login boleh: return true;
        });
    }
}
