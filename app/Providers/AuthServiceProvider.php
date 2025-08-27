<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Form;
use App\Policies\FormPolicy;
use App\Models\Department;
use App\Policies\DepartmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Form::class => FormPolicy::class,
        Department::class => DepartmentPolicy::class,
    ];

    public function boot(): void
{
    $this->registerPolicies();

    // Boleh melakukan approval jika super_admin atau dept_admin pada departemen form-nya
    \Illuminate\Support\Facades\Gate::define('entry-approve', function ($user, \App\Models\FormEntry $entry) {
        return $user->isSuperAdmin() || $user->isDeptAdminOf($entry->form->department_id);
    });
}
}
