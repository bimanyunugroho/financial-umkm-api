<?php

namespace App\DTO\Transaction;

final class TransactionFilterDTO
{
    public function __construct(
        public readonly ?string $type          = null,
        public readonly ?string $categoryId    = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $dateFrom      = null,
        public readonly ?string $dateTo        = null,
        public readonly ?float  $minAmount     = null,
        public readonly ?float  $maxAmount     = null,
        public readonly ?string $description   = null,
        public readonly string  $sortBy        = 'date',
        public readonly string  $sortDir       = 'desc',
        public readonly int     $perPage       = 15,
    ) {}

    public static function fromRequest(array $query): self
    {
        return new self(
            type:          $query['filter']['type']           ?? null,
            categoryId:    $query['filter']['category_id']    ?? null,
            paymentMethod: $query['filter']['payment_method'] ?? null,
            dateFrom:      $query['filter']['date_from']      ?? null,
            dateTo:        $query['filter']['date_to']        ?? null,
            minAmount:     isset($query['filter']['min_amount']) ? (float) $query['filter']['min_amount'] : null,
            maxAmount:     isset($query['filter']['max_amount']) ? (float) $query['filter']['max_amount'] : null,
            description:   $query['filter']['description']    ?? null,
            sortBy:        ltrim($query['sort'] ?? '-date', '-'),
            sortDir:       str_starts_with($query['sort'] ?? '-date', '-') ? 'desc' : 'asc',
            perPage:       min((int) ($query['per_page'] ?? 15), 50),
        );
    }
}
