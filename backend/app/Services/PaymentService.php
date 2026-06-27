<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Payments\Contracts\PaymentMethodHandler;
use App\Payments\Methods\CashOnDeliveryPaymentMethod;
use App\Payments\Methods\MobileMoneyPaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly CashOnDeliveryPaymentMethod $cashOnDelivery,
        private readonly MobileMoneyPaymentMethod $mobileMoney,
    ) {}

    public function initiate(Order $order, string $method, array $context): Payment
    {
        return DB::transaction(function () use ($order, $method, $context): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (in_array($lockedOrder->status, ['cancelled', 'delivered'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Payments cannot be initiated for this order.'],
                ]);
            }

            if (Payment::query()->where('order_id', $lockedOrder->id)->where('status', 'completed')->exists()) {
                throw ValidationException::withMessages(['order' => ['This order has already been paid.']]);
            }

            if (Payment::query()->where('order_id', $lockedOrder->id)->where('method', $method)
                ->whereIn('status', ['pending', 'processing'])->exists()) {
                throw ValidationException::withMessages([
                    'method' => ['An active payment attempt already exists for this method.'],
                ]);
            }

            $data = $this->handler($method)->prepare($lockedOrder, $context);

            return Payment::query()->create([
                'order_id' => $lockedOrder->id,
                'method' => $data->method,
                'provider' => $data->provider,
                'transaction_id' => $data->transactionId,
                'amount' => $lockedOrder->grand_total,
                'currency' => 'UGX',
                'status' => $data->status,
                'meta' => $data->meta,
            ]);
        });
    }

    public function updateCashOnDeliveryStatus(Payment $payment, string $status): Payment
    {
        return DB::transaction(function () use ($payment, $status): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = Order::query()->lockForUpdate()->findOrFail($lockedPayment->order_id);

            if ($lockedPayment->method !== 'cash_on_delivery' || $lockedPayment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => ['Only pending cash-on-delivery payments can be updated here.'],
                ]);
            }

            if ($status === 'completed' && $order->status !== 'delivered') {
                throw ValidationException::withMessages([
                    'status' => ['Cash on delivery can only be completed after the order is delivered.'],
                ]);
            }

            $lockedPayment->update([
                'status' => $status,
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            if ($status === 'completed') {
                Payment::query()
                    ->where('order_id', $order->id)
                    ->where('id', '!=', $lockedPayment->id)
                    ->whereNotIn('status', ['completed', 'cancelled', 'failed'])
                    ->update(['status' => 'cancelled']);
            }

            return $lockedPayment->refresh();
        });
    }

    private function handler(string $method): PaymentMethodHandler
    {
        return match ($method) {
            'cash_on_delivery' => $this->cashOnDelivery,
            'mobile_money' => $this->mobileMoney,
            default => throw ValidationException::withMessages(['method' => ['Unsupported payment method.']]),
        };
    }
}
