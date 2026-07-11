<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['sometimes', 'required', 'string', 'max:100', 'unique:products,sku,'.$this->route('product')?->id],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }
}
