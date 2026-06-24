<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash     = 'cash';
    case Transfer = 'transfer';
    case Qris     = 'qris';
    case Ewallet  = 'ewallet';
    case Credit   = 'credit';

    public function label(): string
    {
        return match($this) {
            self::Cash     => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::Qris     => 'QRIS',
            self::Ewallet  => 'E-Wallet',
            self::Credit   => 'Kartu Kredit',
        };
    }
}