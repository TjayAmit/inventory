<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'cashier_id' => ['required', 'exists:users,id'],
            'order_date' => ['required', 'date'],
            'order_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:pending,confirmed,paid,shipped,completed,cancelled,refunded'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'change_amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,partial,paid,refunded'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
