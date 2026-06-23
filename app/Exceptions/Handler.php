<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(fn (Throwable $e) => null);
    }

    public function render($request, Throwable $e): JsonResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException          => $this->validation($e),
            $e instanceof AuthenticationException      => $this->error(401, 'Unauthenticated. Silakan login terlebih dahulu.'),
            $e instanceof ResourceNotFoundException    => $this->error(404, $e->getMessage()),
            $e instanceof ForbiddenException           => $this->error(403, $e->getMessage()),
            $e instanceof ConflictException            => $this->error(409, $e->getMessage()),
            $e instanceof TooManyRequestsException     => $this->error(429, $e->getMessage()),
            $e instanceof ServiceUnavailableException  => $this->error(503, $e->getMessage()),
            $e instanceof UnauthorizedException        => $this->error(401, $e->getMessage()),
            $e instanceof ModelNotFoundException       => $this->error(404, 'Data tidak ditemukan.'),
            $e instanceof NotFoundHttpException        => $this->error(404, 'Endpoint tidak ditemukan.'),
            $e instanceof MethodNotAllowedHttpException => $this->error(405, 'HTTP method tidak diizinkan.'),
            $e instanceof HttpException                => $this->error($e->getStatusCode(), $e->getMessage() ?: 'HTTP error.'),
            default                                    => $this->serverError($e),
        };
    }

    private function validation(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Data yang dikirim tidak valid.',
            'errors'  => $e->errors(),
        ], 422);
    }

    private function error(int $status, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private function serverError(Throwable $e): JsonResponse
    {
        $debug = config('app.debug');

        return response()->json([
            'success' => false,
            'message' => $debug ? $e->getMessage() : 'Terjadi kesalahan pada server.',
            'trace'   => $debug
                ? collect(explode("\n", $e->getTraceAsString()))->take(10)->values()
                : null,
        ], 500);
    }
}
