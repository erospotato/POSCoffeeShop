<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function changeRole(User $user, User $model): bool
    {
        // Users cannot change their own role
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Users cannot deactivate themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Prevent deactivating the last active administrator
        if ($model->isAdmin()) {
            $adminCount = User::whereHas('role', function ($q) {
                $q->where('slug', 'admin');
            })->where('is_active', true)->count();

            if ($adminCount <= 1) {
                return false;
            }
        }

        return $user->isAdmin();
    }
}
