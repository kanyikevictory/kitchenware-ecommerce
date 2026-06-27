<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency' => $this->currency,
            'status' => $this->status,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenLoaded('items', fn (): int => $this->items->count()),
            'quantity_total' => $this->whenLoaded('items', fn (): int => $this->items->sum('quantity')),
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'updated_at' => $this->updated_at,
        ];
    }
}
