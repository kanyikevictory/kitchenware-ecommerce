<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => 'cash_on_delivery',
            'provider' => null,
            'transaction_id' => Str::uuid()->toString(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'status' => 'pending',
            'paid_at' => null,
            'meta' => [],
        ];
    }
}
