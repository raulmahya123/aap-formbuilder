<?php

namespace App\Support;

use Illuminate\Http\Request;

final class SiteSelection
{
    public static function id(Request $request): ?int
    {
        $value = $request->query->has('site_id') ? $request->query('site_id') : $request->session()->get('active_site_id');
        if ($value === null || $value === '') return null;
        abort_unless(filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0, 422);
        $id = (int) $value;
        abort_unless($request->user()?->hasSiteAccess($id), 403);
        return $id;
    }
}
