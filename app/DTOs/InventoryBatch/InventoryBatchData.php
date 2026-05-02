<?php

namespace App\DTOs\InventoryBatch;

use Illuminate\Http\Request;

class InventoryBatchData
{
    public function __construct(
        public readonly int $inventory_id,
        public readonly string $batch_number,
        public readonly ?string $expiry_date = null,
        public readonly int $quantity = 0,
        public readonly int $quantity_sold = 0,
        public readonly int $quantity_remaining = 0,
        public readonly float $unit_cost = 0.0000,
        public readonly float $total_cost = 0.00,
        public readonly string $received_date,
        public readonly ?string $supplier_batch_ref = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            inventory_id: $request->validated('inventory_id'),
            batch_number: $request->validated('batch_number'),
            expiry_date: $request->validated('expiry_date'),
            quantity: $request->validated('quantity', 0),
            quantity_sold: $request->validated('quantity_sold', 0),
            quantity_remaining: $request->validated('quantity_remaining', 0),
            unit_cost: $request->validated('unit_cost', 0.0000),
            total_cost: $request->validated('total_cost', 0.00),
            received_date: $request->validated('received_date'),
            supplier_batch_ref: $request->validated('supplier_batch_ref'),
            notes: $request->validated('notes'),
        );
    }

    public static function fromModel(InventoryBatch $batch): self
    {
        return new self(
            inventory_id: $batch->inventory_id,
            batch_number: $batch->batch_number,
            expiry_date: $batch->expiry_date?->format('Y-m-d'),
            quantity: $batch->quantity,
            quantity_sold: $batch->quantity_sold,
            quantity_remaining: $batch->quantity_remaining,
            unit_cost: $batch->unit_cost,
            total_cost: $batch->total_cost,
            received_date: $batch->received_date->format('Y-m-d'),
            supplier_batch_ref: $batch->supplier_batch_ref,
            notes: $batch->notes,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
