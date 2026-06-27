<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('product') ?? Product::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'inactive'])],
            'is_featured' => ['sometimes', 'boolean'],
            'images' => ['sometimes', 'array', 'max:8'],
            'images.*' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('6mb')->dimensions(Rule::dimensions()->minWidth(300)->minHeight(300))],
        ];
    }
}
