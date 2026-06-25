<?php

namespace App\DTO\Report;

use App\Enums\ReportPeriod;


final class ReportFilterDTO
{
    public function __construct(
        public readonly ReportPeriod $period,
        public readonly int          $year,
        public readonly int          $month,
        public readonly ?string      $dateFrom = null,
        public readonly ?string      $dateTo   = null,
        public readonly int          $months   = 6,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            period:   ReportPeriod::tryFrom($validated['period'] ?? 'monthly') ?? ReportPeriod::Monthly,
            year:     (int) ($validated['year']     ?? now()->year),
            month:    (int) ($validated['month']    ?? now()->month),
            dateFrom: $validated['date_from']        ?? null,
            dateTo:   $validated['date_to']          ?? null,
            months:   min((int) ($validated['months'] ?? 6), 12),
        );
    }
}
