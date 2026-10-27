<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class SapRecordActions
{
    public static function canDelete(?User $user, Model $record): bool
    {
        return $user && $user->isAdmin() && $user->canUseFeature('hse')
            && ($user->isSuperAdmin() || $user->hasSiteAccess((int) $record->site_id))
            && in_array($record->status, ['Draft', 'Returned', 'Revised'], true)
            && ($user->isSuperAdmin() || in_array($user->id, array_filter([
                $record->getAttribute('inspector_id'), $record->getAttribute('observer_id'), $record->getAttribute('reporter_id'),
            ]), true));
    }
}
