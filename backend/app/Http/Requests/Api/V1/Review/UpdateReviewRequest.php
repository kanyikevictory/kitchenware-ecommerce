<?php

namespace App\Http\Requests\Api\V1\Review;

class UpdateReviewRequest extends StoreReviewRequest
{
    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'comment' => ['sometimes', 'string', 'min:10', 'max:5000'],
        ];
    }
}
