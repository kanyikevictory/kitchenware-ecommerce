<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $this->update($user, $review);
    }

    public function manage(User $user, ?Review $review = null): bool
    {
        return in_array($user->role?->slug, ['admin', 'super-admin'], true);
    }
}
