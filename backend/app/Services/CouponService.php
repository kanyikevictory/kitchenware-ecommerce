<?php

namespace App\Services;

use App\Coupons\Data\CouponApplication;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function evaluateAmount(string $code, string $eligibleAmount, bool $lock = false): CouponApplication
    {
        return $this->evaluate($code, $this->toCents($eligibleAmount), $lock);
    }

    public function evaluate(string $code, int $eligibleAmountCents, bool $lock = false): CouponApplication
    {
        $query = Coupon::query()->where('code', strtoupper(trim($code)));

        if ($lock) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if (! $coupon || ! $coupon->is_active) {
            throw $this->invalid('This coupon is invalid or inactive.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw $this->invalid('This coupon is not active yet.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw $this->invalid('This coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            throw $this->invalid('This coupon has reached its usage limit.');
        }

        if ($eligibleAmountCents < $this->toCents((string) $coupon->minimum_order_amount)) {
            throw $this->invalid("The order does not meet this coupon's minimum amount.");
        }

        $discountCents = $coupon->type === 'percentage'
            ? intdiv($eligibleAmountCents * $this->toCents((string) $coupon->value), 10000)
            : $this->toCents((string) $coupon->value);

        return new CouponApplication($coupon, min($discountCents, $eligibleAmountCents));
    }

    public function recordUsage(Coupon $coupon): void
    {
        $coupon->update(['usage_count' => $coupon->usage_count + 1]);
    }

    private function invalid(string $message): ValidationException
    {
        return ValidationException::withMessages(['coupon_code' => [$message]]);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
