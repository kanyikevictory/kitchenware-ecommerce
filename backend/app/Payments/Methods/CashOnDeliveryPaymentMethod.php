<?php

namespace App\Payments\Methods;

use App\Models\Order;
use App\Payments\Contracts\PaymentMethodHandler;
use App\Payments\Data\PaymentData;
use Illuminate\Support\Str;

class CashOnDeliveryPaymentMethod implements PaymentMethodHandler
{
    public function method(): string
    {
        return 'cash_on_delivery';
    }

    public function prepare(Order $order, array $context): PaymentData
    {
        return new PaymentData(
            method: $this->method(),
            provider: null,
            transactionId: 'COD-'.Str::upper((string) Str::ulid()),
            status: 'pending',
            meta: ['collection' => 'on_delivery'],
        );
    }
}
