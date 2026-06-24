<?php

namespace App\Repositories\Eloquent;

use App\DTO\Transaction\TransactionFilterDTO;
use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionEloquent implements TransactionInterface
{
    public function paginate(string $userId, TransactionFilterDTO $filter): Collection|LengthAwarePaginator
    {
        $query = Transaction::with(['category:id,name,type,icon,color'])
            ->where('user_id', $userId);

        if ($filter->type) {
            $query->where('type', $filter->type);
        }

        if ($filter->categoryId) {
            $query->where('category_id', $filter->categoryId);
        }

        if ($filter->paymentMethod) {
            $query->where('payment_method', $filter->paymentMethod);
        }

        if ($filter->dateFrom) {
            $query->where('date', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo) {
            $query->where('date', '<=', $filter->dateTo);
        }

        if ($filter->minAmount !== null) {
            $query->where('amount', '>=', $filter->minAmount);
        }

        if ($filter->maxAmount !== null) {
            $query->where('amount', '<=', $filter->maxAmount);
        }

        if ($filter->description) {
            $query->where('description', 'ilike', '%' . $filter->description . '%');
        }

        $allowedSorts = ['date', 'amount', 'created_at'];
        $sortBy       = in_array($filter->sortBy, $allowedSorts) ? $filter->sortBy : 'date';

        return $query
            ->orderBy($sortBy, $filter->sortDir)
            ->paginate($filter->perPage)
            ->withQueryString();
    }

    public function findForUser(string $id, string $userId): ?Transaction
    {
        return Transaction::with(['category'])
            ->where('user_id', $userId)
            ->withTrashed()
            ->find($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction->fresh(['category']);
    }

    public function delete(Transaction $transaction): bool
    {
        return (bool) $transaction->delete();
    }

    public function restore(string $id, string $userId): bool
    {
        return (bool) Transaction::withTrashed()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->restore();
    }

    public function sumByType(string $userId, string $type, string $from, string $to): float
    {
        return (float) Transaction::where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');
    }

    public function dailyBreakdown(string $userId, string $from, string $to): Collection
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                date,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END)      AS inflow,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END)      AS outflow,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE -amount END) AS net
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function groupedByCategory(string $userId, string $from, string $to): Collection
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->with('category:id,name,icon,color,type')
            ->selectRaw("
                category_id,
                type,
                COUNT(*)       AS transaction_count,
                SUM(amount)    AS total_amount,
                AVG(amount)    AS avg_amount
            ")
            ->groupBy('category_id', 'type')
            ->orderByDesc('total_amount')
            ->get();
    }

    public function monthlyTrend(string $userId, int $months): Collection
    {
        $from = now()->subMonths($months - 1)->startOfMonth()->toDateString();

        return Transaction::where('user_id', $userId)
            ->where('date', '>=', $from)
            ->selectRaw("
                TO_CHAR(date, 'YYYY-MM')                                       AS month,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END)         AS income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END)         AS expense,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE -amount END)   AS profit
            ")
            ->groupByRaw("TO_CHAR(date, 'YYYY-MM')")
            ->orderByRaw("TO_CHAR(date, 'YYYY-MM')")
            ->get();
    }
}
