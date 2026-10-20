<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if ($user->isCashier()) {
            return $order->user_id === $user->id || $order->cashier_shift_id === $user->activeShift()?->id;
        }

        if ($user->isBarista()) {
            return in_array($order->status->value, ['confirmed', 'preparing', 'ready']);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isCashier();
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if ($user->isCashier()) {
            // Cashiers cannot modify paid or completed or cancelled orders
            return $order->user_id === $user->id && $order->payment_status->value === 'unpaid';
        }

        return false;
    }

    public function updateKitchenStatus(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isBarista();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($order->isCompleted() || $order->isCancelled()) {
            return false;
        }

        // Only Admin or Manager can authorize cancellations of confirmed/paid orders
        return $user->isAdmin() || $user->isManager();
    }

    public function refund(User $user, Order $order): bool
    {
        if ($order->isCancelled() || $order->totalPaid() <= 0) {
            return false;
        }

        // Only Admin or Manager can issue refunds
        return $user->isAdmin() || $user->isManager();
    }
}
