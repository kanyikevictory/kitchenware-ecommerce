<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 10, 500);

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->unique()->bothify('KST-####-###')),
            'brand' => fake()->company(),
            'description' => fake()->paragraphs(2, true),
            'price' => $price,
            'discount_price' => fake()->optional()->randomFloat(2, 1, $price),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'is_featured' => fake()->boolean(20),
            'status' => 'active',
        ];
    }
}
