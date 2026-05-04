<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class InventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $inventoryId = $this->route('inventory')?->id;

        return [
            'product_id' => ['required', 'exists:products,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'quantity_on_hand' => ['required', 'integer', 'min:0'],
            'quantity_reserved' => ['nullable', 'integer', 'min:0'],
            'average_cost' => ['nullable', 'numeric', 'min:0'],
            'last_count_date' => ['nullable', 'date'],
            'last_received_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'branch_id' => 'branch',
            'quantity_on_hand' => 'quantity on hand',
            'quantity_reserved' => 'quantity reserved',
            'average_cost' => 'average cost',
            'last_count_date' => 'last count date',
            'last_received_date' => 'last received date',
        ];
    }
}
