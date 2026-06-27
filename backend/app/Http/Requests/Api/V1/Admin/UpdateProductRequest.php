<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['category_id', 'name', 'price', 'sku', 'stock_quantity'] as $field) {
            $rules[$field] = array_values(array_diff($rules[$field], ['required']));
            array_unshift($rules[$field], 'sometimes');
        }

        $rules['sku'] = ['sometimes', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->route('product'))];
        $rules['discount_price'] = ['sometimes', 'nullable', 'numeric', 'min:0'];

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $product = $this->route('product');
            $price = (float) ($this->input('price', $product?->price ?? 0));
            $discount = $this->has('discount_price') ? $this->input('discount_price') : $product?->discount_price;

            if ($discount !== null && (float) $discount >= $price) {
                $validator->errors()->add('discount_price', 'The discount price must be less than the price.');
            }
        }];
    }
}
