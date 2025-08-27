<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FormEntry;

class FormEntryPolicy
{
    /**
     * Auto-allow untuk Super Admin di SEMUA ability.
     */
    public function before(User $user, string $ability)
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return null; // lanjut ke method ability spesifik
    }

    /**
     * Boleh lihat daftar Entries (index).
     * Aturan: semua user yang login.
     */
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Boleh lihat satu entry.
     * Aturan: super_admin / dept_admin departemen form / pemilik entry.
     */
    public function view(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return ($entry->user_id === $user->id)
            || (method_exists($user, 'isDeptAdminOf') && $user->isDeptAdminOf($entry->form->department_id));
    }

    /**
     * Membuat entry baru (store).
     * Aturan default: semua user login boleh membuat isian (sesuaikan jika perlu).
     * Jika ingin membatasi per-departemen, pakai Gate khusus/context di controller.
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Update entry.
     * Aturan: dept_admin departemen form (atau super admin via before()).
     * (Tambahkan owner boleh edit jika kebijakan Anda mengizinkan.)
     */
    public function update(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return method_exists($user, 'isDeptAdminOf')
            && $user->isDeptAdminOf($entry->form->department_id);
    }

    /**
     * Hapus entry.
     * Aturan: dept_admin departemen form (atau super admin via before()).
     */
    public function delete(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return method_exists($user, 'isDeptAdminOf')
            && $user->isDeptAdminOf($entry->form->department_id);
    }

    /**
     * (Opsional) Restore soft-deleted entry.
     */
    public function restore(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return method_exists($user, 'isDeptAdminOf')
            && $user->isDeptAdminOf($entry->form->department_id);
    }

    /**
     * (Opsional) Force delete.
     */
    public function forceDelete(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return method_exists($user, 'isDeptAdminOf')
            && $user->isDeptAdminOf($entry->form->department_id);
    }

    /**
     * (Opsional) Approve entry — jika Anda ingin authorize via policy, bukan Gate.
     * Di controller: $this->authorize('approve', $entry);
     */
    public function approve(User $user, FormEntry $entry): bool
    {
        $entry->loadMissing('form:id,department_id');

        return method_exists($user, 'isDeptAdminOf')
            && $user->isDeptAdminOf($entry->form->department_id);
    }
}
