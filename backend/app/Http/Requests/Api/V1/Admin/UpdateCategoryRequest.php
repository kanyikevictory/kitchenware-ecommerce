<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['name'] = ['sometimes', 'string', 'max:255'];
        $rules['parent_id'] = [
            'sometimes',
            'nullable',
            'integer',
            Rule::exists('categories', 'id')->whereNull('deleted_at'),
            Rule::notIn([$this->route('category')?->id]),
        ];

        return $rules;
    }
}
