<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCouponRequest extends StoreCouponRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['code', 'type', 'value'] as $field) {
            $rules[$field] = array_values(array_diff($rules[$field], ['required']));
            array_unshift($rules[$field], 'sometimes');
        }

        $rules['code'] = ['sometimes', 'string', 'max:100', Rule::unique('coupons', 'code')->ignore($this->route('coupon'))];

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $coupon = $this->route('coupon');
            $type = $this->input('type', $coupon?->type);
            $value = (float) $this->input('value', $coupon?->value);
            $startsAt = $this->input('starts_at', $coupon?->starts_at);
            $expiresAt = $this->input('expires_at', $coupon?->expires_at);

            if ($type === 'percentage' && $value > 100) {
                $validator->errors()->add('value', 'Percentage coupons cannot exceed 100%.');
            }

            if ($startsAt && $expiresAt && strtotime((string) $expiresAt) <= strtotime((string) $startsAt)) {
                $validator->errors()->add('expires_at', 'The expiry date must be after the start date.');
            }
        }];
    }
}
