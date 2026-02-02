<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CcmReport;

class CcmReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, CcmReport $report): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, CcmReport $report): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CcmReport $report): bool
    {
        return $user->isAdmin();
    }
}
