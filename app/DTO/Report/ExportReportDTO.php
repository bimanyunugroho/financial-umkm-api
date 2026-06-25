<?php

namespace App\DTO\Report;

final class ExportReportDTO
{
    public function __construct(
        public readonly string       $userId,
        public readonly string       $format,
        public readonly ReportFilterDTO $filter,
    ) {}

    public static function fromRequest(array $validated, string $userId): self
    {
        return new self(
            userId: $userId,
            format: $validated['format'],
            filter: ReportFilterDTO::fromRequest($validated),
        );
    }
}
