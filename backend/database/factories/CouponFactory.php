<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('SAVE-###')),
            'type' => 'percentage',
            'value' => fake()->numberBetween(5, 25),
            'minimum_order_amount' => 0,
            'usage_limit' => fake()->optional()->numberBetween(1, 1000),
            'usage_count' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
