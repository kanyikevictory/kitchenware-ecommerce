<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WishlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id' => $this->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->price,
                'discount_price' => $product->discount_price,
                'effective_price' => $product->discount_price ?? $product->price,
                'stock_quantity' => $product->stock_quantity,
                'in_stock' => $product->stock_quantity > 0,
                'is_available' => ! $product->trashed()
                    && $product->status === 'active'
                    && $product->category?->is_active === true,
                'primary_image_url' => $product->primaryImage
                    ? Storage::disk('public')->url($product->primaryImage->path)
                    : null,
            ],
            'added_at' => $this->created_at,
        ];
    }
}
