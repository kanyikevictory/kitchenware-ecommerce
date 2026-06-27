<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 1000);

        return [
            'order_number' => 'ORD-'.Str::upper(fake()->unique()->bothify('########')),
            'user_id' => User::factory(),
            'shipping_address_id' => null,
            'coupon_id' => null,
            'shipping_first_name' => fake()->firstName(),
            'shipping_last_name' => fake()->lastName(),
            'shipping_phone' => fake()->e164PhoneNumber(),
            'shipping_country' => fake()->country(),
            'shipping_state' => fake()->state(),
            'shipping_city' => fake()->city(),
            'shipping_address_line_1' => fake()->streetAddress(),
            'shipping_address_line_2' => fake()->optional()->secondaryAddress(),
            'shipping_postal_code' => fake()->postcode(),
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'tax_total' => 0,
            'grand_total' => $subtotal,
            'status' => 'pending',
            'notes' => null,
            'placed_at' => now(),
        ];
    }
}
