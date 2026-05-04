<?php

namespace App\DTOs\PurchaseOrder;

use Illuminate\Http\Request;

class PurchaseOrderData
{
    public function __construct(
        public readonly string $order_number,
        public readonly int $supplier_id,
        public readonly int $branch_id,
        public readonly int $created_by,
        public readonly string $order_date,
        public readonly ?string $expected_date = null,
        public readonly string $status = 'draft',
        public readonly float $subtotal = 0.00,
        public readonly float $tax_amount = 0.00,
        public readonly float $total_amount = 0.00,
        public readonly ?string $notes = null,
        public readonly ?string $cancelled_at = null,
        public readonly ?int $cancelled_by = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            order_number: $request->validated('order_number'),
            supplier_id: $request->validated('supplier_id'),
            branch_id: $request->validated('branch_id'),
            created_by: $request->validated('created_by'),
            order_date: $request->validated('order_date'),
            expected_date: $request->validated('expected_date'),
            status: $request->validated('status', 'draft'),
            subtotal: $request->validated('subtotal'),
            tax_amount: $request->validated('tax_amount'),
            total_amount: $request->validated('total_amount'),
            notes: $request->validated('notes'),
            cancelled_at: $request->validated('cancelled_at'),
            cancelled_by: $request->validated('cancelled_by'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
