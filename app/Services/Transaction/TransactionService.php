<?php

namespace App\Services\Transaction;

use App\DTO\Transaction\StoreTransactionDTO;
use App\DTO\Transaction\TransactionFilterDTO;
use App\DTO\Transaction\UpdateTransactionDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TransactionService
{
    public function __construct(
        private readonly TransactionInterface $transactionRepo,
    ) {}

    public function list(string $userId, TransactionFilterDTO $filter): Collection|LengthAwarePaginator
    {
        return $this->transactionRepo->paginate($userId, $filter);
    }

    public function findOrFail(string $id, string $userId): Transaction
    {
        $transaction = $this->transactionRepo->findForUser($id, $userId);

        if (! $transaction) {
            throw new ResourceNotFoundException('Transaksi tidak ditemukan.');
        }

        return $transaction;
    }

    public function store(StoreTransactionDTO $dto): Transaction
    {
        $transaction = $this->transactionRepo->create($dto->toArray());
        $this->bustCache($dto->userId);

        return $transaction->load(['category', 'attachments']);
    }

    public function update(string $id, string $userId, UpdateTransactionDTO $dto): Transaction
    {
        $transaction = $this->findOrFail($id, $userId);

        if ($transaction->trashed()) {
            throw new ResourceNotFoundException('Transaksi tidak ditemukan.');
        }

        $updated = $this->transactionRepo->update($transaction, $dto->toArray());
        $this->bustCache($userId);

        return $updated;
    }

    public function delete(string $id, string $userId): void
    {
        $transaction = $this->findOrFail($id, $userId);

        if ($transaction->trashed()) {
            throw new ResourceNotFoundException('Transaksi sudah dihapus.');
        }

        $this->transactionRepo->delete($transaction);
        $this->bustCache($userId);
    }

    public function restore(string $id, string $userId): void
    {
        $restored = $this->transactionRepo->restore($id, $userId);

        if (! $restored) {
            throw new ResourceNotFoundException('Transaksi tidak ditemukan atau belum dihapus.');
        }

        $this->bustCache($userId);
    }

    private function bustCache(string $userId): void
    {
        foreach (['summary', 'pl', 'cf', 'trend', 'bycat'] as $type) {
            Cache::forget("report:{$type}:{$userId}:" . now()->format('Y-m-d'));
            Cache::forget("report:{$type}:{$userId}:" . now()->year);
        }
    }
}
