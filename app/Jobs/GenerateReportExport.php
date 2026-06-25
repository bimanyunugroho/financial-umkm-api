<?php

namespace App\Jobs;

use App\DTO\Report\ExportReportDTO;
use App\Exports\ProfitLossExport;
use App\Models\User;
use App\Services\Report\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class GenerateReportExport implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly ExportReportDTO $dto,
        private readonly string          $jobId,
    ) {
        $this->onQueue('exports');
    }

    public function handle(ReportService $reportService): void
    {
        $statusKey = "export:status:{$this->jobId}";

        try {
            Cache::put($statusKey, array_merge(
                Cache::get($statusKey, []),
                ['status' => 'processing', 'started_at' => now()->toIso8601String()]
            ), 3600);

            $user = User::findOrFail($this->dto->userId);
            $data = $reportService->profitLoss($this->dto->userId, $this->dto->filter);

            $filename = sprintf(
                'laporan-keuangan-%s-%s-%s.%s',
                Str::slug($user->business_name),
                $this->dto->filter->period->value,
                now()->format('Ymd-His'),
                $this->dto->format
            );

            $filePath = "exports/{$user->id}/{$filename}";

            if ($this->dto->format === 'pdf') {
                $this->generatePdf($data, $user, $filePath);
            } else {
                $this->generateExcel($data, $user, $filePath);
            }

            Cache::put($statusKey, [
                'status'       => 'done',
                'job_id'       => $this->jobId,
                'file_path'    => $filePath,
                'filename'     => $filename,
                'format'       => $this->dto->format,
                'completed_at' => now()->toIso8601String(),
            ], 3600);

        } catch (\Throwable $e) {
            Log::error('Export job failed', [
                'job_id'  => $this->jobId,
                'user_id' => $this->dto->userId,
                'error'   => $e->getMessage(),
            ]);

            Cache::put($statusKey, [
                'status'    => 'failed',
                'job_id'    => $this->jobId,
                'error'     => 'Export gagal. Silakan coba lagi.',
                'failed_at' => now()->toIso8601String(),
            ], 3600);

            throw $e;
        }
    }

    private function generatePdf(array $data, User $user, string $filePath): void
    {
        $html = view('exports.profit-loss-pdf', [
            'data'      => $data,
            'user'      => $user,
            'generated' => now()->translatedFormat('d F Y H:i'),
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'         => 'DejaVu Sans',
                'isRemoteEnabled'     => false,
                'isHtml5ParserEnabled'=> true,
            ]);

        Storage::put($filePath, $pdf->output());
    }

    private function generateExcel(array $data, User $user, string $filePath): void
    {
        $export = new ProfitLossExport($data, $user);
        Excel::store($export, $filePath, 'local');
    }
}
