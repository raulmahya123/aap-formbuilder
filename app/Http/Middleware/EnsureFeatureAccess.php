<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = $request->route()?->getName();
        if ($user && !$user->canUseFeatureRoute($name)) {
            if (in_array($name, ['dashboard', 'admin.dashboard'], true) && !$request->expectsJson()) {
                return redirect()->route('profile.edit');
            }
            abort(403, 'Fitur ini tidak diberikan untuk akun Anda.');
        }
        return $next($request);
    }
}
