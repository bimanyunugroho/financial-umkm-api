<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\EnsureUserNotBanned;
use App\Http\Middleware\IdempotencyKey;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            SecurityHeaders::class,
            RequestLogger::class,
            SanitizeInput::class
        ]);

        $middleware->alias([
            'not.banned' => EnsureUserNotBanned::class,
            'idempotent' => IdempotencyKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof ThrottleRequestsException) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

                return response()->json([
                    'success'     => false,
                    'message'     => $retryAfter
                        ? "Terlalu banyak request. Coba lagi dalam {$retryAfter} detik."
                        : 'Terlalu banyak request. Coba lagi nanti.',
                    'retry_after' => $retryAfter ? (int) $retryAfter : null,
                ], 429, $e->getHeaders());
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return app(Handler::class)->render($request, $e);
            }
        });
    })->create();
