<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income  = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match($this) {
            self::Income  => 'Pemasukan',
            self::Expense => 'Pengeluaran',
        };
    }

    public function opposite(): self
    {
        return match($this) {
            self::Income  => self::Expense,
            self::Expense => self::Income,
        };
    }
}
