<?php

// app/Policies/FormPolicy.php
namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function view(User $user, Form $form): bool
    {
        if ($user->isSuperAdmin()) return true;
        $inDepartment = in_array((int) $form->department_id, $user->departmentIds(), true);
        $inSite = !$form->site_id || $user->hasSiteAccess((int) $form->site_id);
        return $inDepartment && $inSite;
    }

    // ⬇️ departmentId dibuat opsional
    public function create(User $user, ?int $departmentId = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        // kalau ada konteks departemen → cek admin departemen tsb
        if (!is_null($departmentId)) {
            return $user->isDeptAdminOf($departmentId);
        }

        // tanpa konteks departemen → default: tolak (atau ubah sesuai kebijakanmu)
        return false;
    }

    public function update(User $user, Form $form): bool
    {
        return $user->isSuperAdmin()
            || ($user->isDeptAdminOf($form->department_id)
                && (!$form->site_id || $user->hasSiteAccess((int) $form->site_id)));
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->isSuperAdmin()
            || ($user->isDeptAdminOf($form->department_id)
                && (!$form->site_id || $user->hasSiteAccess((int) $form->site_id)));
    }

    public function submit(User $user, Form $form): bool
    {
        return $this->view($user, $form) && $form->is_active;
    }
}
