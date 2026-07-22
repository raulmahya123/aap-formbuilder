<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('api-auth.username');
        $password = config('api-auth.password');

        if (!$username || !$password) {
            return response()->json([
                'success' => false,
                'message' => 'API credentials not configured.',
            ], 500);
        }

        $providedUser = $request->getUser();
        $providedPass = $request->getPassword();

        if (!hash_equals($username, (string) $providedUser) || !hash_equals($password, (string) $providedPass)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid username or password.',
            ], 401)->header('WWW-Authenticate', 'Basic realm="Mandala API"');
        }

        return $next($request);
    }
}
