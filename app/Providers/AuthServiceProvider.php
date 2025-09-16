<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\{Form, Department, FormEntry, QaThread, Document,
    Site, IndicatorGroup, Indicator, IndicatorDaily, IndicatorValue,
    Contract, UserSiteAccess};
use App\Policies\{FormPolicy, DepartmentPolicy, FormEntryPolicy, QaThreadPolicy, DocumentPolicy,
    SitePolicy, IndicatorGroupPolicy, IndicatorPolicy, IndicatorDailyPolicy, IndicatorValuePolicy,
    ContractPolicy, UserSiteAccessPolicy};

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Form::class           => FormPolicy::class,
        Department::class     => DepartmentPolicy::class,
        FormEntry::class      => FormEntryPolicy::class,
        QaThread::class       => QaThreadPolicy::class,
        Document::class       => DocumentPolicy::class,
        Site::class           => SitePolicy::class,
        IndicatorGroup::class => IndicatorGroupPolicy::class,
        Indicator::class      => IndicatorPolicy::class,
        IndicatorDaily::class => IndicatorDailyPolicy::class,
        IndicatorValue::class => IndicatorValuePolicy::class,
        Contract::class       => ContractPolicy::class,
        UserSiteAccess::class => UserSiteAccessPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin: auto-allow
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });

        Gate::define('entry-approve', function ($user, FormEntry $entry) {
            return (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || (method_exists($user, 'isDeptAdminOf') && $user->isDeptAdminOf($entry->form->department_id));
        });

        Gate::define('is-admin', function ($user) {
            return method_exists($user, 'isAdmin') && $user->isAdmin();
        });

        Gate::define('site-access', function ($user, $site) {
            $siteId = is_numeric($site) ? (int) $site : ($site->id ?? null);
            if (!$siteId) return false;

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) return true;
            if (method_exists($user, 'sites')) {
                return $user->sites()->where('site_id', $siteId)->exists();
            }
            return false;
        });

        Gate::define('daily.manage', function ($user, $site = null) {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) return true;
            if ($site === null) return false;
            return Gate::forUser($user)->allows('site-access', $site);
        });

        Gate::define('daily-input', function ($user, $site) {
            return Gate::forUser($user)->allows('site-access', $site);
        });

        Gate::define('contract-upload', function ($user) {
            return method_exists($user, 'isAdmin') && $user->isAdmin();
        });
    }
}
