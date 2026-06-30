<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('viewDashboard', User::class);
    }

    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
        ];
    }
}
