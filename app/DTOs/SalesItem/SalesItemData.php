<?php

namespace App\DTOs\SalesItem;

use Illuminate\Http\Request;

class SalesItemData
{
    public function __construct(
        public readonly int $sales_order_id,
        public readonly int $product_id,
        public readonly ?int $inventory_batch_id = null,
        public readonly int $quantity = 1,
        public readonly float $unit_price,
        public readonly float $unit_cost,
        public readonly float $discount_amount = 0.00,
        public readonly float $tax_amount = 0.00,
        public readonly float $total_price,
        public readonly float $total_cost,
        public readonly float $profit,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            sales_order_id: $request->validated('sales_order_id'),
            product_id: $request->validated('product_id'),
            inventory_batch_id: $request->validated('inventory_batch_id'),
            quantity: $request->validated('quantity', 1),
            unit_price: $request->validated('unit_price'),
            unit_cost: $request->validated('unit_cost'),
            discount_amount: $request->validated('discount_amount', 0.00),
            tax_amount: $request->validated('tax_amount', 0.00),
            total_price: $request->validated('total_price'),
            total_cost: $request->validated('total_cost'),
            profit: $request->validated('profit'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
