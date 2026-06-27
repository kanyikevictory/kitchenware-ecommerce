<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', Coupon::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'unique:coupons,code'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'minimum_order_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('type') === 'percentage' && (float) $this->input('value') > 100) {
                $validator->errors()->add('value', 'Percentage coupons cannot exceed 100%.');
            }

            if ($this->filled('starts_at') && $this->filled('expires_at')
                && strtotime((string) $this->input('expires_at')) <= strtotime((string) $this->input('starts_at'))) {
                $validator->errors()->add('expires_at', 'The expiry date must be after the start date.');
            }
        }];
    }
}
