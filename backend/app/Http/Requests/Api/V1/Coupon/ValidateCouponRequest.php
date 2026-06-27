<?php

namespace App\Http\Requests\Api\V1\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['coupon_code' => strtoupper(trim((string) $this->input('coupon_code')))]);
    }

    public function rules(): array
    {
        return ['coupon_code' => ['required', 'string', 'max:100']];
    }
}
