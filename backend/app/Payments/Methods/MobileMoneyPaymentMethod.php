<?php

namespace App\Payments\Methods;

use App\Models\Order;
use App\Payments\Contracts\PaymentMethodHandler;
use App\Payments\Data\PaymentData;
use Illuminate\Support\Str;

class MobileMoneyPaymentMethod implements PaymentMethodHandler
{
    public function method(): string
    {
        return 'mobile_money';
    }

    public function prepare(Order $order, array $context): PaymentData
    {
        $phone = (string) $context['phone'];

        return new PaymentData(
            method: $this->method(),
            provider: null,
            transactionId: 'MM-'.Str::upper((string) Str::ulid()),
            status: 'pending_configuration',
            meta: [
                'provider_status' => 'not_submitted',
                'masked_phone' => $this->maskPhone($phone),
            ],
        );
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4).str_repeat('*', max(0, strlen($phone) - 7)).substr($phone, -3);
    }
}
