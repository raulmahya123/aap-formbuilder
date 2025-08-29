<?php

namespace App\Policies;

use App\Models\{Document, DocumentAcl, User};

class DocumentPolicy
{
    /**
     * Super Admin auto-allow untuk semua ability.
     * Return true → bypass cek lain; return null → lanjut cek detail.
     */
    public function before(User $user, string $ability)
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Helper: apakah user adalah owner dokumen.
     */
    protected function isOwner(User $user, Document $doc): bool
    {
        return (int) $doc->owner_id === (int) $user->id;
    }

    /**
     * Helper: cek ACL/Departemen/Owner untuk permission tertentu.
     */
    protected function hasPerm(User $user, Document $doc, string $perm): bool
    {
        // Owner: full access
        if ($this->isOwner($user, $doc)) return true;

        // Satu departemen: auto allow untuk view & export
        if ($doc->department_id && $user->department_id === $doc->department_id) {
            if (in_array($perm, ['view', 'export'], true)) return true;
        }

        // Cek ACL spesifik (user / department)
        return DocumentAcl::where('document_id', $doc->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('department_id', $user->department_id);
            })
            ->where('perm', $perm)
            ->exists();
    }

    /**
     * Kadang "boleh melihat" cukup jika punya salah satu perm lain (edit/delete/share/export).
     * Supaya fleksibel, view juga diizinkan jika ada ACL perm lain.
     */
    protected function canViewByAnyPerm(User $user, Document $doc): bool
    {
        if ($this->isOwner($user, $doc)) return true;

        // Satu departemen juga dianggap boleh lihat (lihat aturan di atas)
        if ($doc->department_id && $user->department_id === $doc->department_id) {
            return true;
        }

        return DocumentAcl::where('document_id', $doc->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('department_id', $user->department_id);
            })
            ->whereIn('perm', ['view', 'edit', 'delete', 'share', 'export'])
            ->exists();
    }

    // ================== Abilities ==================

    /**
     * List dokumen (index).
     * Biasanya semua user login boleh lihat daftar (nanti filter di query).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Lihat detail dokumen.
     */
    public function view(User $user, Document $doc): bool
    {
        // izinkan jika punya perm view, atau perm lain yang implisit memberi read
        return $this->hasPerm($user, $doc, 'view') || $this->canViewByAnyPerm($user, $doc);
    }

    /**
     * Membuat dokumen baru.
     * - Super Admin sudah auto-allow via before()
     * - Jika punya method isAdmin(), pakai itu; jika tidak ada, fallback: user aktif & punya department.
     *   (Silakan sesuaikan sesuai kebutuhan organisasi Anda.)
     */
    public function create(User $user): bool
    {
        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        // Fallback aman (ganti sesuai aturan Anda):
        return true;
    }

    /**
     * Update dokumen.
     */
    public function update(User $user, Document $doc): bool
    {
        // Owner atau ACL 'edit'
        return $this->isOwner($user, $doc) || $this->hasPerm($user, $doc, 'edit');
    }

    /**
     * Hapus dokumen.
     */
    public function delete(User $user, Document $doc): bool
    {
        // Owner atau ACL 'delete'
        return $this->isOwner($user, $doc) || $this->hasPerm($user, $doc, 'delete');
    }

    /**
     * Bagikan akses.
     */
    public function share(User $user, Document $doc): bool
    {
        // Owner atau ACL 'share'
        return $this->isOwner($user, $doc) || $this->hasPerm($user, $doc, 'share');
    }

    /**
     * Export dokumen (PDF/Word/CSV).
     */
    public function export(User $user, Document $doc): bool
    {
        // Owner, satu departemen (auto-allow export), atau ACL 'export'
        if ($this->isOwner($user, $doc)) return true;

        if ($doc->department_id && $user->department_id === $doc->department_id) {
            return true;
        }

        return $this->hasPerm($user, $doc, 'export');
    }

    // (Opsional) restore/forceDelete jika pakai SoftDeletes
    public function restore(User $user, Document $doc): bool
    {
        // Batasi ke owner/ACL khusus (ubah sesuai kebutuhan)
        return $this->isOwner($user, $doc) || $this->hasPerm($user, $doc, 'restore');
    }

    public function forceDelete(User $user, Document $doc): bool
    {
        return $this->isOwner($user, $doc) || $this->hasPerm($user, $doc, 'forceDelete');
    }
}
