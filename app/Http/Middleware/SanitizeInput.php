<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should NOT be modified (passwords, tokens, base64, etc.)
     */
    private array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sanitized = $this->clean($request->all());
        $request->merge($sanitized);

        return $next($request);
    }

    private function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->clean($v), $value);
        }

        if (is_string($value)) {
            // Trim whitespace
            $value = trim($value);

            // Strip null bytes (security: prevents path traversal tricks)
            $value = str_replace("\0", '', $value);

            // Normalize multiple spaces to single space
            $value = preg_replace('/\s+/', ' ', $value);

            return $value;
        }

        return $value;
    }
}
