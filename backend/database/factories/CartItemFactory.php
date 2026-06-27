<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 10, 500);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => 0,
            'total_price' => $unitPrice * $quantity,
        ];
    }
}
