<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $this->route('product')->id,
            'barcode' => 'nullable|string|size:13|regex:/^[0-9]+$/',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'cost_price' => 'nullable|numeric|min:0.01|max:999999.99',
            'category_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'unit' => 'string|max:20',
            'weight' => 'nullable|numeric|min:0|max:999999.999',
            'volume' => 'nullable|numeric|min:0|max:999999.999',
            'brand' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:100',
            'reorder_point' => 'nullable|integer|min:0|max:1000000',
            'max_stock' => 'nullable|integer|min:0|max:1000000',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.max' => 'The product name may not be greater than 200 characters.',
            'product_code.required' => 'The product code is required.',
            'product_code.max' => 'The product code may not be greater than 50 characters.',
            'product_code.unique' => 'The product code has already been taken.',
            'barcode.size' => 'The barcode must be exactly 13 characters.',
            'barcode.regex' => 'The barcode must contain only digits.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price must be at least 0.01.',
            'price.max' => 'The price may not be greater than 999999.99.',
            'cost_price.min' => 'The cost price must be at least 0.01.',
            'cost_price.max' => 'The cost price may not be greater than 999999.99.',
            'category_id.exists' => 'The selected category does not exist.',
            'weight.max' => 'The weight may not be greater than 999999.999.',
            'volume.max' => 'The volume may not be greater than 999999.999.',
            'brand.max' => 'The brand may not be greater than 100 characters.',
            'manufacturer.max' => 'The manufacturer may not be greater than 100 characters.',
            'supplier.max' => 'The supplier may not be greater than 100 characters.',
            'reorder_point.min' => 'The reorder point must be at least 0.',
            'reorder_point.max' => 'The reorder point may not be greater than 1000000.',
            'max_stock.min' => 'The max stock must be at least 0.',
            'max_stock.max' => 'The max stock may not be greater than 1000000.',
            'notes.max' => 'The notes may not be greater than 2000 characters.',
        ];
    }
}
