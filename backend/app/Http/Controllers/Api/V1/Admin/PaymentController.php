<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateCashOnDeliveryStatusRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function updateStatus(UpdateCashOnDeliveryStatusRequest $request, Payment $payment): JsonResponse
    {
        Gate::authorize('manage', $payment);
        $payment = $this->paymentService->updateCashOnDeliveryStatus(
            $payment,
            $request->validated('status'),
        );

        return response()->json([
            'message' => 'Cash-on-delivery payment status updated successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }
}
