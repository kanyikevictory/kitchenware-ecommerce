<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('coupon_code')) {
            $this->merge(['coupon_code' => strtoupper(trim((string) $this->input('coupon_code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', 'integer', Rule::exists('shipping_addresses', 'id')->whereNull('deleted_at')],
            'notes' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ];
    }
}
