<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::UNPAID => 'bg-amber-100 text-amber-800',
            self::PARTIALLY_PAID => 'bg-yellow-100 text-yellow-800',
            self::PAID => 'bg-emerald-100 text-emerald-800',
            self::REFUNDED => 'bg-rose-100 text-rose-800',
        };
    }
}
