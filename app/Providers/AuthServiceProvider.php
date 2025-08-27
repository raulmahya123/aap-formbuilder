<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\{Form, Department, FormEntry};
use App\Policies\{FormPolicy, DepartmentPolicy, FormEntryPolicy};

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Map model ke policy.
     */
    protected $policies = [
        Form::class       => FormPolicy::class,
        Department::class => DepartmentPolicy::class,
        FormEntry::class  => FormEntryPolicy::class, // ← daftar policy entries
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin auto-allow semua ability
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });

        // Gate khusus approval entry (opsional)
        Gate::define('entry-approve', function ($user, FormEntry $entry) {
            return $user->isSuperAdmin() || $user->isDeptAdminOf($entry->form->department_id);
        });
    }
}
