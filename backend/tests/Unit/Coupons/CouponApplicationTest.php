<?php

namespace Tests\Unit\Coupons;

use App\Coupons\Data\CouponApplication;
use App\Models\Coupon;
use PHPUnit\Framework\TestCase;

class CouponApplicationTest extends TestCase
{
    public function test_application_carries_coupon_and_exact_cent_discount(): void
    {
        $coupon = new Coupon(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);
        $application = new CouponApplication($coupon, 12550);

        $this->assertSame($coupon, $application->coupon);
        $this->assertSame(12550, $application->discountCents);
    }
}
