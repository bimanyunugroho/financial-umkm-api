<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    private array $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'token'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Only log slow requests (>500ms) or errors in production
        // In local, log everything for debugging
        if (app()->environment('local') || $response->getStatusCode() >= 400 || $duration > 500) {
            Log::channel('daily')->info('API Request', [
                'method'      => $request->method(),
                'url'         => $request->fullUrl(),
                'ip'          => $request->ip(),
                'user_id'     => $request->user()?->id,
                'status'      => $response->getStatusCode(),
                'duration_ms' => $duration,
                'user_agent'  => $request->userAgent(),
                'body'        => $this->sanitizeBody($request->all()),
            ]);
        }

        // Inject timing header for debugging
        $response->headers->set('X-Response-Time', $duration . 'ms');

        return $response;
    }

    private function sanitizeBody(array $body): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '***';
            }
        }

        return $body;
    }
}
