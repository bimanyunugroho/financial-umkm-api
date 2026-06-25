<?php

namespace App\Services\Report;

use App\DTO\Report\ExportReportDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Jobs\GenerateReportExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportService
{
    public function dispatch(ExportReportDTO $dto): array
    {
        $jobId    = Str::uuid()->toString();
        $cacheKey = "export:status:{$jobId}";

        Cache::put($cacheKey, [
            'status'    => 'queued',
            'job_id'    => $jobId,
            'format'    => $dto->format,
            'user_id'   => $dto->userId,
            'queued_at' => now()->toIso8601String(),
        ], 3600);

        GenerateReportExport::dispatch($dto, $jobId)->onQueue('exports');

        return [
            'job_id'     => $jobId,
            'status'     => 'queued',
            'message'    => 'Export sedang diproses.',
            'status_url' => url("/api/v1/reports/export/status/{$jobId}"),
            'queued_at'  => now()->toIso8601String(),
        ];
    }

    public function status(string $jobId): array
    {
        $status = Cache::get("export:status:{$jobId}");

        if (! $status) {
            throw new ResourceNotFoundException('Export job tidak ditemukan atau sudah kadaluarsa.');
        }

        if ($status['status'] === 'done' && isset($status['file_path'])) {
            $status['download_url'] = Storage::temporaryUrl(
                $status['file_path'],
                now()->addMinutes(30),
            );
        }

        return $status;
    }
}
