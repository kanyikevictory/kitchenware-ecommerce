<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['mobile_money', 'cash_on_delivery'])],
            'phone' => ['required_if:method,mobile_money', 'nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
        ];
    }
}
