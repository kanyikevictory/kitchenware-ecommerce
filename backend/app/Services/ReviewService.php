<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function create(User $user, Product $product, array $attributes): Review
    {
        $purchased = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (! $purchased) {
            throw ValidationException::withMessages([
                'product' => ['Only customers with a delivered purchase can review this product.'],
            ]);
        }

        if (Review::withTrashed()->where('user_id', $user->id)->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages(['product' => ['You have already reviewed this product.']]);
        }

        return Review::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'is_approved' => false,
        ]);
    }

    public function update(Review $review, array $attributes): Review
    {
        $review->update([...$attributes, 'is_approved' => false]);

        return $review->refresh();
    }
}
