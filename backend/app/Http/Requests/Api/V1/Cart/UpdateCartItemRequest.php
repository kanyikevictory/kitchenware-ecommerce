<?php

namespace App\Http\Requests\Api\V1\Cart;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('cartItem');

        return $item instanceof CartItem && $item->cart?->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'min:1', 'max:100']];
    }
}
