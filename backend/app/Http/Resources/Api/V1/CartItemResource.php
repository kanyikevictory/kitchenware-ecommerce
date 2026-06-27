<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'sku' => $this->product->sku,
                'stock_quantity' => $this->product->stock_quantity,
                'status' => $this->product->status,
                'is_available' => ! $this->product->trashed()
                    && $this->product->status === 'active'
                    && $this->product->category?->is_active === true,
                'primary_image_url' => $this->product->primaryImage
                    ? Storage::disk('public')->url($this->product->primaryImage->path)
                    : null,
            ],
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'total_price' => $this->total_price,
        ];
    }
}
