<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function manage(User $user, ?Coupon $coupon = null): bool
    {
        return in_array($user->role?->slug, ['admin', 'super-admin'], true);
    }
}
