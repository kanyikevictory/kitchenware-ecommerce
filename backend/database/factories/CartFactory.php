<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => (string) Str::uuid(),
            'currency' => 'USD',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'grand_total' => 0,
            'status' => 'active',
        ];
    }
}
