<?php

namespace App\Enums;

enum RoleType: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';
    case BARISTA = 'barista';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
            self::BARISTA => 'Barista',
        };
    }
}
