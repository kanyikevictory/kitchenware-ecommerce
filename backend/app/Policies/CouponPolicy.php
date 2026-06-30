<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function manage(User $user, ?Coupon $coupon = null): bool
    {
        return $user->hasPermission('coupons.manage');
    }
}
