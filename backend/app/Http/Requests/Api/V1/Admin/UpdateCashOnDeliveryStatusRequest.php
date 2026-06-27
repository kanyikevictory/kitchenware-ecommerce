<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCashOnDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('payment') ?? Payment::class);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['completed', 'cancelled'])]];
    }
}
