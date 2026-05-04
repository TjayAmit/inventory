<?php

namespace App\DTOs\PurchaseOrderItem;

use Illuminate\Http\Request;

class PurchaseOrderItemData
{
    public function __construct(
        public readonly int $purchase_order_id,
        public readonly int $product_id,
        public readonly ?int $purchase_request_id = null,
        public readonly int $quantity_ordered,
        public readonly int $quantity_received = 0,
        public readonly float $unit_cost,
        public readonly float $total_cost,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            purchase_order_id: $request->validated('purchase_order_id'),
            product_id: $request->validated('product_id'),
            purchase_request_id: $request->validated('purchase_request_id'),
            quantity_ordered: $request->validated('quantity_ordered'),
            quantity_received: $request->validated('quantity_received', 0),
            unit_cost: $request->validated('unit_cost'),
            total_cost: $request->validated('total_cost'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
