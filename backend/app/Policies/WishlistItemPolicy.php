<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WishlistItem;

class WishlistItemPolicy
{
    public function delete(User $user, WishlistItem $wishlistItem): bool
    {
        return $wishlistItem->wishlist?->user_id === $user->id;
    }
}
