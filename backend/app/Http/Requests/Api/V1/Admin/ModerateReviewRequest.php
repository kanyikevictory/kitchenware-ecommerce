<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ModerateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('review') ?? Review::class);
    }

    public function rules(): array
    {
        return ['is_approved' => ['required', 'boolean']];
    }
}
