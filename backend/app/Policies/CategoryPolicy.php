<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAnyAdmin(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->isAdministrator($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->isAdministrator($user);
    }

    private function isAdministrator(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'super-admin'], true);
    }
}
