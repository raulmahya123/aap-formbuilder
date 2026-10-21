<?php

namespace App\Policies;

use App\Models\QaMessage;
use App\Models\User;

class QaMessagePolicy
{
    /**
     * Boleh mengedit pesan?
     * Hanya pemilik pesan sendiri, dan pesan belum dihapus.
     */
    public function update(User $user, QaMessage $message): bool
    {
        if (!$user->is_active) {
            return false;
        }

        return $message->user_id === $user->id;
    }

    /**
     * Boleh menghapus pesan?
     * Pemilik pesan sendiri, atau admin grup / super_admin jika bukan di personal chat orang lain.
     */
    public function delete(User $user, QaMessage $message): bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($message->user_id === $user->id) {
            return true;
        }

        // Jika group / site chat, super_admin atau thread manager bisa delete_any
        if ($message->thread && $message->thread->type !== 'personal') {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return $message->thread->participants()
                ->where('users.id', $user->id)
                ->whereIn('qa_participants.chat_role', ['admin', 'owner'])
                ->exists();
        }

        return false;
    }

    /**
     * Boleh pin pesan di thread?
     */
    public function pin(User $user, QaMessage $message): bool
    {
        if (!$user->is_active || !$message->thread) {
            return false;
        }

        if ($message->thread->type === 'personal') {
            return $message->thread->participants()->where('users.id', $user->id)->exists();
        }

        return $user->can('manage', $message->thread);
    }
}
