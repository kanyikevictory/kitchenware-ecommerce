<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'status' => $this->status,
            'coupon' => $this->whenLoaded('coupon', fn (): ?array => $this->coupon ? [
                'code' => $this->coupon->code,
                'type' => $this->coupon->type,
                'value' => $this->coupon->value,
            ] : null),
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'shipping_total' => $this->shipping_total,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'notes' => $this->notes,
            'shipping_address' => [
                'first_name' => $this->shipping_first_name,
                'last_name' => $this->shipping_last_name,
                'phone' => $this->shipping_phone,
                'country' => $this->shipping_country,
                'state' => $this->shipping_state,
                'city' => $this->shipping_city,
                'address_line_1' => $this->shipping_address_line_1,
                'address_line_2' => $this->shipping_address_line_2,
                'postal_code' => $this->shipping_postal_code,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'placed_at' => $this->placed_at,
            'created_at' => $this->created_at,
        ];
    }
}
