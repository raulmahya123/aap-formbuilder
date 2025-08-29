<?php

namespace App\Policies;

use App\Models\{Document, DocumentAcl, User};

class DocumentPolicy
{
    /**
     * Helper untuk cek permission di ACL.
     */
    protected function hasPerm(User $user, Document $doc, string $perm): bool
    {
        // kalau super admin, auto allow
        if (method_exists($user,'isSuperAdmin') && $user->isSuperAdmin()) return true;

        // pemilik dokumen, auto allow semua
        if ($doc->owner_id === $user->id) return true;

        // kalau user 1 departemen & perm view/export → izinkan
        if ($doc->department_id && $user->department_id === $doc->department_id) {
            if (in_array($perm, ['view','export'])) return true;
        }

        // cek ACL (akses spesifik user / department)
        return DocumentAcl::where('document_id',$doc->id)
            ->where(function($q) use($user){
                $q->where('user_id',$user->id)
                  ->orWhere('department_id',$user->department_id);
            })
            ->where('perm',$perm)
            ->exists();
    }

    public function view(User $user, Document $doc): bool
    {
        return $this->hasPerm($user, $doc, 'view');
    }

    public function create(User $user): bool
    {
        // misal: admin & super admin boleh buat
        return (method_exists($user,'isAdmin') && $user->isAdmin())
            || (method_exists($user,'isSuperAdmin') && $user->isSuperAdmin());
    }

    public function update(User $user, Document $doc): bool
    {
        return $this->hasPerm($user, $doc, 'edit');
    }

    public function delete(User $user, Document $doc): bool
    {
        return $this->hasPerm($user, $doc, 'delete');
    }

    public function share(User $user, Document $doc): bool
    {
        return $this->hasPerm($user, $doc, 'share');
    }

    public function export(User $user, Document $doc): bool
    {
        return $this->hasPerm($user, $doc, 'export');
    }
}
