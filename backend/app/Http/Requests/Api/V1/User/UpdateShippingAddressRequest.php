<?php

namespace App\Http\Requests\Api\V1\User;

class UpdateShippingAddressRequest extends StoreShippingAddressRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules): array => array_merge(['sometimes'], array_diff($rules, ['required'])))
            ->all();
    }
}
