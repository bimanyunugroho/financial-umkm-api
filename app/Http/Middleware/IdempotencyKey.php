<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only applies to state-changing methods
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        // Validate UUID format
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key)) {
            return response()->json([
                'success' => false,
                'message' => 'Idempotency-Key harus berformat UUID.',
            ], 422);
        }

        $userId    = $request->user()?->id ?? $request->ip();
        $cacheKey  = "idempotency:{$userId}:{$key}";

        // Return cached response if key already used
        if ($cached = Cache::get($cacheKey)) {
            return response()->json(
                json_decode($cached['body'], true),
                $cached['status']
            )->header('X-Idempotency-Replayed', 'true');
        }

        $response = $next($request);

        // Cache only successful responses (2xx) for 24 hours
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Cache::put($cacheKey, [
                'body'   => $response->getContent(),
                'status' => $response->getStatusCode(),
            ], 86400);
        }

        return $response;
    }
}
