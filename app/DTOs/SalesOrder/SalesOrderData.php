<?php

namespace App\DTOs\SalesOrder;

use Illuminate\Http\Request;

class SalesOrderData
{
    public function __construct(
        public readonly string $order_number,
        public readonly ?int $customer_id = null,
        public readonly int $branch_id,
        public readonly int $created_by,
        public readonly ?int $cashier_id = null,
        public readonly string $status = 'pending',
        public readonly string $payment_status = 'pending',
        public readonly string $order_type = 'sale',
        public readonly string $order_date,
        public readonly float $subtotal = 0.00,
        public readonly float $tax_amount = 0.00,
        public readonly float $discount_amount = 0.00,
        public readonly float $shipping_amount = 0.00,
        public readonly float $total_amount = 0.00,
        public readonly float $paid_amount = 0.00,
        public readonly float $change_amount = 0.00,
        public readonly ?string $notes = null,
        public readonly ?string $internal_notes = null,
        public readonly ?string $customer_reference = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            order_number: $request->validated('order_number'),
            customer_id: $request->validated('customer_id'),
            branch_id: $request->validated('branch_id'),
            created_by: $request->validated('created_by'),
            cashier_id: $request->validated('cashier_id'),
            status: $request->validated('status', 'pending'),
            payment_status: $request->validated('payment_status', 'pending'),
            order_type: $request->validated('order_type', 'sale'),
            order_date: $request->validated('order_date'),
            subtotal: $request->validated('subtotal', 0.00),
            tax_amount: $request->validated('tax_amount', 0.00),
            discount_amount: $request->validated('discount_amount', 0.00),
            shipping_amount: $request->validated('shipping_amount', 0.00),
            total_amount: $request->validated('total_amount', 0.00),
            paid_amount: $request->validated('paid_amount', 0.00),
            change_amount: $request->validated('change_amount', 0.00),
            notes: $request->validated('notes'),
            internal_notes: $request->validated('internal_notes'),
            customer_reference: $request->validated('customer_reference'),
        );
    }

    public static function fromModel(SalesOrder $salesOrder): self
    {
        return new self(
            order_number: $salesOrder->order_number,
            customer_id: $salesOrder->customer_id,
            branch_id: $salesOrder->branch_id,
            created_by: $salesOrder->created_by,
            cashier_id: $salesOrder->cashier_id,
            status: $salesOrder->status,
            payment_status: $salesOrder->payment_status,
            order_type: $salesOrder->order_type,
            order_date: $salesOrder->order_date->format('Y-m-d H:i:s'),
            subtotal: $salesOrder->subtotal,
            tax_amount: $salesOrder->tax_amount,
            discount_amount: $salesOrder->discount_amount,
            shipping_amount: $salesOrder->shipping_amount,
            total_amount: $salesOrder->total_amount,
            paid_amount: $salesOrder->paid_amount,
            change_amount: $salesOrder->change_amount,
            notes: $salesOrder->notes,
            internal_notes: $salesOrder->internal_notes,
            customer_reference: $salesOrder->customer_reference,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
