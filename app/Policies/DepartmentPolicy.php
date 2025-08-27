<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->departments()->exists();
    }

    public function view(User $user, Department $department): bool
    {
        return $user->isSuperAdmin()
            || $user->departments()->where('department_id', $department->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isSuperAdmin() || $user->isDeptAdminOf($department->id);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isSuperAdmin();
    }
}
