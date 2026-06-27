<?php

namespace App\Coupons\Data;

use App\Models\Coupon;

final readonly class CouponApplication
{
    public function __construct(
        public Coupon $coupon,
        public int $discountCents,
    ) {}
}
