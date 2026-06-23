<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    // ── 2xx ───────────────────────────────────────────────────────────────

    protected function ok(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return $this->respond(true, 200, $message, $data);
    }

    protected function created(mixed $data = null, string $message = 'Resource berhasil dibuat'): JsonResponse
    {
        return $this->respond(true, 201, $message, $data);
    }

    protected function noContent(string $message = 'Berhasil'): JsonResponse
    {
        return $this->respond(true, 200, $message, null);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page'  => $paginator->currentPage(),
                'per_page'      => $paginator->perPage(),
                'total'         => $paginator->total(),
                'last_page'     => $paginator->lastPage(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
                'has_more'      => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'links'         => [
                    'first' => $paginator->url(1),
                    'last'  => $paginator->url($paginator->lastPage()),
                ],
            ],
        ], 200);
    }

    // ── 4xx ───────────────────────────────────────────────────────────────

    protected function badRequest(string $message = 'Bad request', mixed $errors = null): JsonResponse
    {
        return $this->respondError(400, $message, $errors);
    }

    protected function unauthorized(string $message = 'Unauthenticated. Silakan login terlebih dahulu.'): JsonResponse
    {
        return $this->respondError(401, $message);
    }

    protected function forbidden(string $message = 'Anda tidak memiliki akses.'): JsonResponse
    {
        return $this->respondError(403, $message);
    }

    protected function notFound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->respondError(404, $message);
    }

    protected function conflict(string $message = 'Konflik data.'): JsonResponse
    {
        return $this->respondError(409, $message);
    }

    protected function unprocessable(mixed $errors, string $message = 'Data yang dikirim tidak valid.'): JsonResponse
    {
        return $this->respondError(422, $message, $errors);
    }

    protected function tooManyRequests(string $message = 'Terlalu banyak request.'): JsonResponse
    {
        return $this->respondError(429, $message);
    }

    // ── 5xx ───────────────────────────────────────────────────────────────

    protected function serverError(string $message = 'Terjadi kesalahan pada server.'): JsonResponse
    {
        return $this->respondError(500, $message);
    }

    protected function serviceUnavailable(string $message = 'Layanan tidak tersedia saat ini.'): JsonResponse
    {
        return $this->respondError(503, $message);
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function respond(bool $success, int $status, string $message, mixed $data): JsonResponse
    {
        $payload = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    private function respondError(int $status, string $message, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
