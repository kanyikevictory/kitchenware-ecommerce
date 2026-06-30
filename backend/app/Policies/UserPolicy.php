<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    public function viewDashboard(User $user): bool
    {
        return $user->hasPermission('dashboard.view');
    }

    public function updateStatus(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($user->role?->slug === 'super-admin') {
            return true;
        }

        return $user->hasPermission('users.update-status') && $target->hasRole('customer');
    }
}
