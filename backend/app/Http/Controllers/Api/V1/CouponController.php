<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Coupon\ValidateCouponRequest;
use App\Models\Cart;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $couponService) {}

    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        $cart = Cart::query()->where('user_id', $request->user()->id)->first();

        if (! $cart || ! $cart->items()->exists()) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        $application = $this->couponService->evaluateAmount(
            $request->validated('coupon_code'),
            (string) $cart->grand_total,
        );
        $eligibleCents = $this->toCents((string) $cart->grand_total);

        return response()->json([
            'message' => 'Coupon is valid.',
            'data' => [
                'code' => $application->coupon->code,
                'type' => $application->coupon->type,
                'value' => $application->coupon->value,
                'eligible_amount' => $cart->grand_total,
                'discount_amount' => $this->fromCents($application->discountCents),
                'total_after_discount' => $this->fromCents($eligibleCents - $application->discountCents),
            ],
        ]);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}
