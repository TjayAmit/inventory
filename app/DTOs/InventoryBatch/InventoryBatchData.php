<?php

namespace App\DTOs\InventoryBatch;

use Illuminate\Http\Request;

class InventoryBatchData
{
    public function __construct(
        public readonly int $inventory_id,
        public readonly string $batch_number,
        public readonly ?int $purchase_order_item_id = null,
        public readonly int $quantity,
        public readonly int $quantity_remaining,
        public readonly float $unit_cost,
        public readonly ?string $manufacture_date = null,
        public readonly ?string $expiry_date = null,
        public readonly string $received_date,
        public readonly int $received_by,
        public readonly ?string $location = null,
        public readonly ?string $notes = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            inventory_id: $request->validated('inventory_id'),
            batch_number: $request->validated('batch_number'),
            purchase_order_item_id: $request->validated('purchase_order_item_id'),
            quantity: $request->validated('quantity'),
            quantity_remaining: $request->validated('quantity_remaining'),
            unit_cost: $request->validated('unit_cost'),
            manufacture_date: $request->validated('manufacture_date'),
            expiry_date: $request->validated('expiry_date'),
            received_date: $request->validated('received_date'),
            received_by: $request->validated('received_by'),
            location: $request->validated('location'),
            notes: $request->validated('notes'),
            is_active: $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
