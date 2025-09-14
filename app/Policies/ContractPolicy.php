<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    /**
     * Lihat daftar kontrak.
     * Bisa diatur: semua user login boleh, atau hanya admin.
     */
    public function viewAny(User $user): bool
    {
        return true; // misalnya semua user login bisa lihat daftar
    }

    /**
     * Lihat kontrak tertentu.
     */
    public function view(User $user, Contract $contract): bool
    {
        // pemilik boleh lihat
        if ($contract->owner_id === $user->id) {
            return true;
        }

        // cek apakah user ada di daftar viewers (ACL)
        return $contract->viewers()->whereKey($user->id)->exists();
    }

    /**
     * Upload kontrak baru.
     */
    public function create(User $user): bool
    {
        return true; // misalnya semua user login boleh upload
        // kalau khusus admin: return $user->is_admin;
    }

    /**
     * Update kontrak (ubah judul, ganti file).
     */
    public function update(User $user, Contract $contract): bool
    {
        return $contract->owner_id === $user->id;
    }

    /**
     * Hapus kontrak.
     */
    public function delete(User $user, Contract $contract): bool
    {
        return $contract->owner_id === $user->id;
    }

    /**
     * Restore kontrak (jika pakai soft delete).
     */
    public function restore(User $user, Contract $contract): bool
    {
        return $contract->owner_id === $user->id;
    }

    /**
     * Hapus permanen kontrak.
     */
    public function forceDelete(User $user, Contract $contract): bool
    {
        return $contract->owner_id === $user->id;
    }

    /**
     * Tambah / cabut akses viewer.
     */
    public function share(User $user, Contract $contract): bool
    {
        return $contract->owner_id === $user->id;
    }
}
