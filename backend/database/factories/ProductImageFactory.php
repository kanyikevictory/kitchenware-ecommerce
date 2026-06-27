<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => fake()->imageUrl(1200, 1200, 'business', true),
            'alt_text' => fake()->sentence(4),
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }
}
