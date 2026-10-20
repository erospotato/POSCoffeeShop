<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Confirmation',
            self::CONFIRMED => 'Confirmed (In Queue)',
            self::PREPARING => 'Preparing (Kitchen)',
            self::READY => 'Ready for Pickup',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-100 text-amber-800 border-amber-300',
            self::CONFIRMED => 'bg-blue-100 text-blue-800 border-blue-300',
            self::PREPARING => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            self::READY => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::COMPLETED => 'bg-gray-100 text-gray-800 border-gray-300',
            self::CANCELLED => 'bg-red-100 text-red-800 border-red-300',
        };
    }
}
