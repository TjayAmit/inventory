<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SalesItemRequest extends FormRequest
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
        $salesItemId = $this->route('sales_item')?->id;

        return [
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'product_id' => ['required', 'exists:products,id'],
            'inventory_batch_id' => ['nullable', 'exists:inventory_batches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
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
            'sales_order_id' => 'sales order',
            'product_id' => 'product',
            'inventory_batch_id' => 'inventory batch',
            'unit_price' => 'unit price',
            'unit_cost' => 'unit cost',
            'discount_amount' => 'discount amount',
            'tax_amount' => 'tax amount',
        ];
    }
}
