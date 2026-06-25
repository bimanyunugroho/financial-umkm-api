<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Report\ExportReportDTO;
use App\DTO\Report\ReportFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ExportReportRequest;
use App\Http\Requests\Report\ReportFilterRequest;
use App\Services\Report\ExportService;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Reports
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly ExportService $exportService,
    ) {}

    /**
     * Dashboard summary for the current month.
     */
    public function summary(Request $request): JsonResponse
    {
        $data = $this->reportService->summary($request->user()->id);

        return $this->ok($data, 'Ringkasan bulan ini.');
    }

    /**
     * Profit & Loss report.
     *
     * @queryParam period string Period: daily|weekly|monthly|yearly|custom. Example: monthly
     * @queryParam year int Year. Example: 2024
     * @queryParam month int Month 1–12. Example: 1
     * @queryParam date_from date Required if period=custom. Example: 2024-01-01
     * @queryParam date_to date Required if period=custom. Example: 2024-03-31
     */
    public function profitLoss(ReportFilterRequest $request): JsonResponse
    {
        $dto  = ReportFilterDTO::fromRequest($request->validated());
        $data = $this->reportService->profitLoss($request->user()->id, $dto);

        return $this->ok($data, 'Laporan laba rugi.');
    }

    /**
     * Cash flow report.
     *
     * @queryParam period string Period type. Example: monthly
     * @queryParam year int Year. Example: 2024
     * @queryParam month int Month. Example: 1
     */
    public function cashFlow(ReportFilterRequest $request): JsonResponse
    {
        $dto  = ReportFilterDTO::fromRequest($request->validated());
        $data = $this->reportService->cashFlow($request->user()->id, $dto);

        return $this->ok($data, 'Laporan arus kas.');
    }

    /**
     * Transactions grouped by category.
     *
     * @queryParam period string Period type. Example: monthly
     * @queryParam year int Year. Example: 2024
     * @queryParam month int Month. Example: 1
     */
    public function byCategory(ReportFilterRequest $request): JsonResponse
    {
        $dto  = ReportFilterDTO::fromRequest($request->validated());
        $data = $this->reportService->byCategory($request->user()->id, $dto);

        return $this->ok($data, 'Laporan per kategori.');
    }

    /**
     * Monthly income vs expense trend.
     *
     * @queryParam months int Number of months to look back (1–12). Default: 6.
     */
    public function trend(ReportFilterRequest $request): JsonResponse
    {
        $dto  = ReportFilterDTO::fromRequest($request->validated());
        $data = $this->reportService->trend($request->user()->id, $dto->months);

        return $this->ok($data, 'Tren bulanan.');
    }

    /**
     * Dispatch async export job (PDF or Excel).
     *
     * @queryParam format string required pdf or xlsx. Example: pdf
     * @queryParam period string Period type. Example: monthly
     * @queryParam year int Year. Example: 2024
     * @queryParam month int Month. Example: 1
     */
    public function export(ExportReportRequest $request): JsonResponse
    {
        $dto    = ExportReportDTO::fromRequest($request->validated(), $request->user()->id);
        $result = $this->exportService->dispatch($dto);

        return $this->ok($result, 'Export dijadwalkan.');
    }

    /**
     * Poll export job status.
     *
     * @urlParam jobId string required The UUID returned by /reports/export.
     */
    public function exportStatus(Request $request, string $jobId): JsonResponse
    {
        $status = $this->exportService->status($jobId);

        return $this->ok($status, 'Status export.');
    }
}
