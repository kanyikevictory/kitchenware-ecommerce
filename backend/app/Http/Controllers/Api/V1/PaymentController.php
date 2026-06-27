<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\InitiatePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Order $order): AnonymousResourceCollection
    {
        Gate::authorize('view', $order);

        return PaymentResource::collection(
            Payment::query()->where('order_id', $order->id)->latest()->get(),
        );
    }

    public function store(InitiatePaymentRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);
        $payment = $this->paymentService->initiate(
            $order,
            $request->validated('method'),
            $request->safe()->only('phone'),
        );
        $isMobileMoney = $payment->method === 'mobile_money';

        return response()->json([
            'message' => $isMobileMoney
                ? 'Mobile Money payment prepared; provider configuration is required before submission.'
                : 'Cash-on-delivery payment prepared successfully.',
            'data' => new PaymentResource($payment),
        ], $isMobileMoney ? Response::HTTP_ACCEPTED : Response::HTTP_CREATED);
    }
}
