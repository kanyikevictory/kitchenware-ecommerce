<?php

namespace App\Payments\Contracts;

use App\Models\Order;
use App\Payments\Data\PaymentData;

interface PaymentMethodHandler
{
    public function method(): string;

    public function prepare(Order $order, array $context): PaymentData;
}
