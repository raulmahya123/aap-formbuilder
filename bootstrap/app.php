<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Tambahan import untuk handler 403
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // 1) Gagal policy/Gate/@can() → AuthorizationException
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            return response()->view('unauthorized', [
                'message' => $e->getMessage(),
            ], 403);
        });

        // 2) abort(403) atau error 403 lain → HttpException dengan status 403
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null; // biarkan handler lain menangani
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            return response()->view('unauthorized', [
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });

        // (opsional) AccessDeniedHttpException (turunan 403)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            return response()->view('unauthorized', [
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });
    })
    ->create();
    