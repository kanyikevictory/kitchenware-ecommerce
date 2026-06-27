<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'super-admin'], true);
    }

    public function updateStatus(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($user->role?->slug === 'super-admin') {
            return true;
        }

        return $user->role?->slug === 'admin' && $target->role?->slug === 'customer';
    }
}
