<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = ['q' => trim((string) $this->input('q'))];

        foreach (['in_stock', 'featured'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->boolean($field);
            }
        }

        foreach (['min_price', 'max_price'] as $field) {
            if ($this->has($field) && is_numeric($this->input($field))) {
                $normalized[$field] = (float) $this->input($field);
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'type' => ['sometimes', Rule::in(['all', 'products', 'categories'])],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'parent_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'brand' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'in_stock' => ['sometimes', 'boolean'],
            'featured' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', Rule::in(['newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'])],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
            'product_page' => ['sometimes', 'integer', 'min:1'],
            'category_page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
