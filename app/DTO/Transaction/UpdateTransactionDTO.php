<?php

namespace App\DTO\Transaction;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;

final class UpdateTransactionDTO
{
    public function __construct(
        public readonly ?TransactionType $type            = null,
        public readonly ?float           $amount          = null,
        public readonly ?string          $categoryId      = null,
        public readonly ?string          $description     = null,
        public readonly ?string          $date            = null,
        public readonly ?PaymentMethod   $paymentMethod   = null,
        public readonly ?string          $notes           = null,
        public readonly ?string          $referenceNumber = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            type:            isset($validated['type'])           ? TransactionType::from($validated['type'])     : null,
            amount:          isset($validated['amount'])         ? (float) $validated['amount']                  : null,
            categoryId:      $validated['category_id']           ?? null,
            description:     $validated['description']           ?? null,
            date:            $validated['date']                  ?? null,
            paymentMethod:   isset($validated['payment_method']) ? PaymentMethod::from($validated['payment_method']) : null,
            notes:           $validated['notes']                 ?? null,
            referenceNumber: $validated['reference_number']      ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type'             => $this->type?->value,
            'amount'           => $this->amount,
            'category_id'      => $this->categoryId,
            'description'      => $this->description,
            'date'             => $this->date,
            'payment_method'   => $this->paymentMethod?->value,
            'notes'            => $this->notes,
            'reference_number' => $this->referenceNumber,
        ], fn ($v) => $v !== null);
    }
}
