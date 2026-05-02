<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name may not be greater than 200 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'sort_order.min' => 'The sort order must be at least 0.',
            'sort_order.max' => 'The sort order may not be greater than 1000000.',
        ];
    }
}
