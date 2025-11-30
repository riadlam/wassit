<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            append: [
                \App\Http\Middleware\SetLocale::class,
            ],
            replace: [
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class => \App\Http\Middleware\VerifyCsrfToken::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated exceptions - redirect to home with login trigger
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            // Always return JSON for broadcasting auth endpoint
            if ($request->is('broadcasting/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // Redirect to home page and trigger login modal
            return redirect()->route('home')->with('show_login', true);
        });
        
        // Handle AccessDeniedHttpException (403) for broadcasting routes - return JSON
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            // Always return JSON for broadcasting routes
            if ($request->is('broadcasting/*')) {
                \Log::info('AccessDeniedHttpException for broadcasting', [
                    'path' => $request->path(),
                    'channel' => $request->input('channel_name'),
                ]);
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            
            // Also return JSON if JSON is expected
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            
            return null; // Let Laravel handle it normally for other routes
        });
    })->create();
