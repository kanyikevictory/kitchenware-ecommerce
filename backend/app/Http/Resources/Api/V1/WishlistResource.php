<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'items' => WishlistItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenLoaded('items', fn (): int => $this->items->count()),
            'updated_at' => $this->updated_at,
        ];
    }
}
