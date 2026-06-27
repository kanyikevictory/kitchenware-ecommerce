<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\CheckoutRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(
            $request->user(),
            $request->integer('shipping_address_id'),
            $request->validated('notes'),
            $request->validated('coupon_code'),
        );

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => new OrderResource($order),
        ], Response::HTTP_CREATED);
    }
}
