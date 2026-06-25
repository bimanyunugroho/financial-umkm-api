<?php

namespace App\Services\Report;

use App\DTO\Report\ReportFilterDTO;
use App\Enums\ReportPeriod;
use App\Repositories\Interfaces\TransactionInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function __construct(
        private readonly TransactionInterface $transactionRepo,
    ) {}

    // ── Summary ────────────────────────────────────────────────────────────

    public function summary(string $userId): array
    {
        $key = "report:summary:{$userId}:" . now()->format('Y-m-d');

        return Cache::remember($key, 300, function () use ($userId) {
            $now       = now();
            $thisStart = $now->copy()->startOfMonth()->toDateString();
            $thisEnd   = $now->copy()->endOfMonth()->toDateString();
            $prevStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
            $prevEnd   = $now->copy()->subMonth()->endOfMonth()->toDateString();

            $thisIncome  = $this->transactionRepo->sumByType($userId, 'income',  $thisStart, $thisEnd);
            $thisExpense = $this->transactionRepo->sumByType($userId, 'expense', $thisStart, $thisEnd);
            $prevIncome  = $this->transactionRepo->sumByType($userId, 'income',  $prevStart, $prevEnd);
            $prevExpense = $this->transactionRepo->sumByType($userId, 'expense', $prevStart, $prevEnd);

            $thisProfit = $thisIncome - $thisExpense;
            $prevProfit = $prevIncome - $prevExpense;

            return [
                'this_month' => [
                    'income'        => $thisIncome,
                    'expense'       => $thisExpense,
                    'profit'        => $thisProfit,
                    'profit_margin' => $thisIncome > 0 ? round(($thisProfit / $thisIncome) * 100, 2) : 0,
                ],
                'comparison_prev_month' => [
                    'income_diff'  => $thisIncome  - $prevIncome,
                    'expense_diff' => $thisExpense - $prevExpense,
                    'profit_diff'  => $thisProfit  - $prevProfit,
                    'income_pct'   => $this->pctChange($prevIncome,  $thisIncome),
                    'expense_pct'  => $this->pctChange($prevExpense, $thisExpense),
                    'profit_pct'   => $this->pctChange($prevProfit,  $thisProfit),
                ],
                'by_category'    => $this->transactionRepo->groupedByCategory($userId, $thisStart, $thisEnd),
                'trend_6_months' => $this->transactionRepo->monthlyTrend($userId, 6),
                'generated_at'   => now()->toIso8601String(),
            ];
        });
    }

    // ── Profit & Loss ──────────────────────────────────────────────────────

    public function profitLoss(string $userId, ReportFilterDTO $dto): array
    {
        [$from, $to, $label] = $this->resolveDates($dto);
        $key = "report:pl:{$userId}:{$from}:{$to}";

        return Cache::remember($key, 600, function () use ($userId, $from, $to, $label, $dto) {
            $income  = $this->transactionRepo->sumByType($userId, 'income',  $from, $to);
            $expense = $this->transactionRepo->sumByType($userId, 'expense', $from, $to);
            $profit  = $income - $expense;

            [$prevFrom, $prevTo] = $this->previousPeriod($from, $to);
            $prevIncome  = $this->transactionRepo->sumByType($userId, 'income',  $prevFrom, $prevTo);
            $prevExpense = $this->transactionRepo->sumByType($userId, 'expense', $prevFrom, $prevTo);
            $prevProfit  = $prevIncome - $prevExpense;

            return [
                'period'        => $label,
                'date_from'     => $from,
                'date_to'       => $to,
                'gross_income'  => $income,
                'total_expense' => $expense,
                'net_profit'    => $profit,
                'profit_margin' => $income > 0 ? round(($profit / $income) * 100, 2) : 0,
                'profit_status' => $profit > 0 ? 'profit' : ($profit < 0 ? 'loss' : 'break_even'),
                'by_category' => $this->transactionRepo->groupedByCategory($userId, $from, $to)->map(function ($item) {
                        return [
                            'category_name'     => $item->category->name ?? 'Lainnya',
                            'type'              => $item->type instanceof \BackedEnum
                                ? $item->type->value
                                : $item->type,
                            'transaction_count' => (int) $item->transaction_count,
                            'total_amount'      => (float) $item->total_amount,
                            'avg_amount'        => (float) $item->avg_amount,
                        ];
                    })
                    ->values()
                    ->all(),
                'comparison'    => [
                    'prev_date_from' => $prevFrom,
                    'prev_date_to'   => $prevTo,
                    'prev_income'    => $prevIncome,
                    'prev_expense'   => $prevExpense,
                    'prev_profit'    => $prevProfit,
                    'income_change'  => $this->pctChange($prevIncome,  $income),
                    'expense_change' => $this->pctChange($prevExpense, $expense),
                    'profit_change'  => $this->pctChange($prevProfit,  $profit),
                ],
                'generated_at'  => now()->toIso8601String(),
            ];
        });
    }

    // ── Cash Flow ──────────────────────────────────────────────────────────

    public function cashFlow(string $userId, ReportFilterDTO $dto): array
    {
        [$from, $to, $label] = $this->resolveDates($dto);
        $key = "report:cf:{$userId}:{$from}:{$to}";

        return Cache::remember($key, 600, function () use ($userId, $from, $to, $label) {
            $daily        = $this->transactionRepo->dailyBreakdown($userId, $from, $to);
            $totalInflow  = (float) $daily->sum('inflow');
            $totalOutflow = (float) $daily->sum('outflow');

            $running = 0;
            $breakdown = $daily->map(function ($row) use (&$running) {
                $running += (float) $row->net;
                return [
                    'date'            => $row->date,
                    'inflow'          => (float) $row->inflow,
                    'outflow'         => (float) $row->outflow,
                    'net'             => (float) $row->net,
                    'running_balance' => $running,
                ];
            });

            return [
                'period'          => $label,
                'date_from'       => $from,
                'date_to'         => $to,
                'total_inflow'    => $totalInflow,
                'total_outflow'   => $totalOutflow,
                'net_flow'        => $totalInflow - $totalOutflow,
                'daily_breakdown' => $breakdown,
                'generated_at'    => now()->toIso8601String(),
            ];
        });
    }

    // ── Trend ──────────────────────────────────────────────────────────────

    public function trend(string $userId, int $months): array
    {
        $key = "report:trend:{$userId}:{$months}";

        return Cache::remember($key, 600, function () use ($userId, $months) {
            return [
                'months'       => $months,
                'data'         => $this->transactionRepo->monthlyTrend($userId, $months),
                'generated_at' => now()->toIso8601String(),
            ];
        });
    }

    // ── By Category ────────────────────────────────────────────────────────

    public function byCategory(string $userId, ReportFilterDTO $dto): array
    {
        [$from, $to, $label] = $this->resolveDates($dto);
        $key = "report:bycat:{$userId}:{$from}:{$to}";

        return Cache::remember($key, 600, function () use ($userId, $from, $to, $label) {
            return [
                'period'       => $label,
                'date_from'    => $from,
                'date_to'      => $to,
                'by_category'  => $this->transactionRepo->groupedByCategory($userId, $from, $to),
                'generated_at' => now()->toIso8601String(),
            ];
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function resolveDates(ReportFilterDTO $dto): array
    {
        return match ($dto->period) {
            ReportPeriod::Daily   => [
                now()->toDateString(),
                now()->toDateString(),
                'Hari ini',
            ],
            ReportPeriod::Weekly  => [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
                'Minggu ini',
            ],
            ReportPeriod::Monthly => [
                Carbon::create($dto->year, $dto->month)->startOfMonth()->toDateString(),
                Carbon::create($dto->year, $dto->month)->endOfMonth()->toDateString(),
                Carbon::create($dto->year, $dto->month)->translatedFormat('F Y'),
            ],
            ReportPeriod::Yearly  => [
                Carbon::create($dto->year, 1, 1)->toDateString(),
                Carbon::create($dto->year, 12, 31)->toDateString(),
                "Tahun {$dto->year}",
            ],
            ReportPeriod::Custom  => [
                $dto->dateFrom,
                $dto->dateTo,
                "Custom ({$dto->dateFrom} s/d {$dto->dateTo})",
            ],
        };
    }

    private function previousPeriod(string $from, string $to): array
    {
        $days     = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $prevFrom = Carbon::parse($from)->subDays($days)->toDateString();
        $prevTo   = Carbon::parse($from)->subDay()->toDateString();

        return [$prevFrom, $prevTo];
    }

    private function pctChange(float $old, float $new): ?float
    {
        if ($old == 0) return $new > 0 ? 100.0 : null;
        return round((($new - $old) / abs($old)) * 100, 2);
    }
}
