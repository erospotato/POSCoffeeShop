<?php

namespace App\Policies;

use App\Models\CashierShift;
use App\Models\User;

class CashierShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isCashier();
    }

    public function view(User $user, CashierShift $shift): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $shift->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Must be Cashier, Manager, or Admin; cannot open a second shift if one is already open
        if ($user->activeShift()) {
            return false;
        }

        return $user->isCashier() || $user->isManager() || $user->isAdmin();
    }

    public function close(User $user, CashierShift $shift): bool
    {
        if ($shift->isClosed()) {
            return false;
        }

        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $shift->user_id === $user->id;
    }

    public function addCashMovement(User $user, CashierShift $shift): bool
    {
        if ($shift->isClosed()) {
            return false;
        }

        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $shift->user_id === $user->id;
    }

    public function review(User $user, CashierShift $shift): bool
    {
        // Only managers or admins can review shifts & discrepancies
        return $user->isAdmin() || $user->isManager();
    }
}
