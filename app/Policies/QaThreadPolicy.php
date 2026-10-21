<?php

namespace App\Policies;

use App\Models\QaThread;
use App\Models\User;

class QaThreadPolicy
{
    /**
     * Boleh melihat daftar thread/chat?
     */
    public function viewAny(User $user): bool
    {
        return (bool) ($user->is_active ?? true);
    }

    /**
     * Boleh melihat thread/chat tertentu?
     * Site checking: jika memiliki site_id, user harus memiliki hak akses ke site tersebut.
     * Personal chat: hanya partisipan (bahkan Super Admin tidak otomatis membaca percakapan personal orang lain untuk menjaga privasi, sesuai Phase 8).
     * Group chat: partisipan atau (jika public site general) user yang berhak di site tersebut.
     */
    public function view(User $user, QaThread $thread): bool
    {
        if (!$user->is_active) {
            return false;
        }

        // Cek batasan site jika thread terikat site (hanya untuk non-personal / group / site channels)
        if ($thread->type !== 'personal' && $thread->site_id && !$user->hasSiteAccess((int) $thread->site_id)) {
            return false;
        }

        // Personal chat (1-on-1): ketat hanya untuk partisipan
        if ($thread->type === 'personal') {
            return $thread->participants()->where('users.id', $user->id)->exists();
        }

        // Public site / general channel
        if ($thread->scope === 'public') {
            return true;
        }

        // Private group: jika partisipan
        if ($thread->participants()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Admin site atau Super Admin untuk grup internal (non-personal) yang membutuhkan audit
        if ($user->isSuperAdmin() && $thread->type !== 'personal') {
            return true;
        }

        return false;
    }

    /**
     * Boleh membuat thread/chat?
     */
    public function create(User $user): bool
    {
        return (bool) ($user->is_active ?? true);
    }

    /**
     * Boleh membalas dalam thread?
     */
    public function reply(User $user, QaThread $thread): bool
    {
        return $this->view($user, $thread);
    }

    /**
     * Boleh mengelola grup (tambah/hapus anggota, ubah info grup)?
     */
    public function manage(User $user, QaThread $thread): bool
    {
        if ($thread->type === 'personal') {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($thread->created_by === $user->id) {
            return true;
        }

        return $thread->participants()
            ->where('users.id', $user->id)
            ->whereIn('qa_participants.chat_role', ['admin', 'owner'])
            ->exists();
    }

    /**
     * Boleh menandai selesai / resolve?
     */
    public function resolve(User $user, QaThread $thread): bool
    {
        return (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || ($thread->created_by === $user->id);
    }
}
