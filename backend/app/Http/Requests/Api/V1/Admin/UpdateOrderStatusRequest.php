<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('order') ?? Order::class);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])]];
    }
}
