<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderStatusController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function cancel(Order $order): JsonResponse
    {
        Gate::authorize('cancel', $order);
        $order = $this->orderService->cancel($order);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}
