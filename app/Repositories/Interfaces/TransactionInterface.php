<?php

namespace App\Repositories\Interfaces;

use App\DTO\Transaction\TransactionFilterDTO;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionInterface
{
    public function paginate(string $userId, TransactionFilterDTO $filter): Collection|LengthAwarePaginator;

    public function findForUser(string $id, string $userId): ?Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;

    public function delete(Transaction $transaction): bool;

    public function restore(string $id, string $userId): bool;

    public function sumByType(string $userId, string $type, string $from, string $to): float;

    public function dailyBreakdown(string $userId, string $from, string $to): Collection;

    public function groupedByCategory(string $userId, string $from, string $to): Collection;

    public function monthlyTrend(string $userId, int $months): Collection;
}
