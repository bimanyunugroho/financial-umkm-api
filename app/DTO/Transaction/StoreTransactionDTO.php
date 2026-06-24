<?php

namespace App\DTO\Transaction;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;

final class StoreTransactionDTO
{
    public function __construct(
        public readonly string          $userId,
        public readonly TransactionType $type,
        public readonly float           $amount,
        public readonly string          $categoryId,
        public readonly string          $description,
        public readonly string          $date,
        public readonly PaymentMethod   $paymentMethod,
        public readonly ?string         $notes           = null,
        public readonly ?string         $referenceNumber = null,
    ) {}

    public static function fromRequest(array $validated, string $userId): self
    {
        return new self(
            userId:          $userId,
            type:            TransactionType::from($validated['type']),
            amount:          (float) $validated['amount'],
            categoryId:      $validated['category_id'],
            description:     $validated['description'],
            date:            $validated['date'],
            paymentMethod:   PaymentMethod::from($validated['payment_method']),
            notes:           $validated['notes']            ?? null,
            referenceNumber: $validated['reference_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'          => $this->userId,
            'type'             => $this->type->value,
            'amount'           => $this->amount,
            'category_id'      => $this->categoryId,
            'description'      => $this->description,
            'date'             => $this->date,
            'payment_method'   => $this->paymentMethod->value,
            'notes'            => $this->notes,
            'reference_number' => $this->referenceNumber,
        ];
    }
}
