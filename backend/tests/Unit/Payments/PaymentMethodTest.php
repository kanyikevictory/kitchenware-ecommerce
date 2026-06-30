<?php

namespace Tests\Unit\Payments;

use App\Models\Order;
use App\Payments\Contracts\PaymentMethodHandler;
use App\Payments\Methods\CashOnDeliveryPaymentMethod;
use App\Payments\Methods\MobileMoneyPaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    public function test_cash_on_delivery_prepares_pending_collection(): void
    {
        $method = new CashOnDeliveryPaymentMethod;
        $data = $method->prepare(new Order, []);

        $this->assertInstanceOf(PaymentMethodHandler::class, $method);
        $this->assertSame('cash_on_delivery', $data->method);
        $this->assertNull($data->provider);
        $this->assertSame('pending', $data->status);
        $this->assertStringStartsWith('COD-', $data->transactionId);
        $this->assertSame('on_delivery', $data->meta['collection']);
    }

    public function test_mobile_money_preparation_masks_phone_and_does_not_claim_submission(): void
    {
        $method = new MobileMoneyPaymentMethod;
        $data = $method->prepare(new Order, ['phone' => '+256700123456']);

        $this->assertInstanceOf(PaymentMethodHandler::class, $method);
        $this->assertSame('mobile_money', $data->method);
        $this->assertSame('pending_configuration', $data->status);
        $this->assertSame('not_submitted', $data->meta['provider_status']);
        $this->assertStringStartsWith('MM-', $data->transactionId);
        $this->assertStringNotContainsString('+256700123456', $data->meta['masked_phone']);
        $this->assertStringEndsWith('456', $data->meta['masked_phone']);
    }

    public function test_transaction_references_are_unique(): void
    {
        $method = new CashOnDeliveryPaymentMethod;

        $first = $method->prepare(new Order, []);
        $second = $method->prepare(new Order, []);

        $this->assertNotSame($first->transactionId, $second->transactionId);
    }
}
