<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->isAdmin();
    }
}
