<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage', Order::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $orders = Order::query()->with('user:id,name,email')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', "%{$search}%"));
            }))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->latest('placed_at')->paginate($perPage)->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        Gate::authorize('manage', $order);

        return new OrderResource($order->load(['items', 'user:id,name,email']));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('manage', $order);
        $order = $this->orderService->updateStatus($order, $request->validated('status'));

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}
