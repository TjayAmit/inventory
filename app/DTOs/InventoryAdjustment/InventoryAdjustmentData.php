<?php

namespace App\DTOs\InventoryAdjustment;

use Illuminate\Http\Request;

class InventoryAdjustmentData
{
    public function __construct(
        public readonly string $adjustment_number,
        public readonly int $product_id,
        public readonly int $branch_id,
        public readonly ?int $inventory_batch_id = null,
        public readonly int $created_by,
        public readonly ?int $approved_by = null,
        public readonly string $adjustment_type = 'count_correction',
        public readonly string $status = 'draft',
        public readonly int $quantity_before = 0,
        public readonly int $quantity_adjusted = 0,
        public readonly int $quantity_after = 0,
        public readonly ?float $unit_cost = null,
        public readonly ?float $total_cost = null,
        public readonly string $reason,
        public readonly ?string $notes = null,
        public readonly ?string $approval_notes = null,
        public readonly string $adjustment_date,
        public readonly ?string $approved_at = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            adjustment_number: $request->validated('adjustment_number'),
            product_id: $request->validated('product_id'),
            branch_id: $request->validated('branch_id'),
            inventory_batch_id: $request->validated('inventory_batch_id'),
            created_by: $request->validated('created_by'),
            approved_by: $request->validated('approved_by'),
            adjustment_type: $request->validated('adjustment_type', 'count_correction'),
            status: $request->validated('status', 'draft'),
            quantity_before: $request->validated('quantity_before', 0),
            quantity_adjusted: $request->validated('quantity_adjusted', 0),
            quantity_after: $request->validated('quantity_after', 0),
            unit_cost: $request->validated('unit_cost'),
            total_cost: $request->validated('total_cost'),
            reason: $request->validated('reason'),
            notes: $request->validated('notes'),
            approval_notes: $request->validated('approval_notes'),
            adjustment_date: $request->validated('adjustment_date'),
            approved_at: $request->validated('approved_at'),
        );
    }

    public static function fromModel(InventoryAdjustment $adjustment): self
    {
        return new self(
            adjustment_number: $adjustment->adjustment_number,
            product_id: $adjustment->product_id,
            branch_id: $adjustment->branch_id,
            inventory_batch_id: $adjustment->inventory_batch_id,
            created_by: $adjustment->created_by,
            approved_by: $adjustment->approved_by,
            adjustment_type: $adjustment->adjustment_type,
            status: $adjustment->status,
            quantity_before: $adjustment->quantity_before,
            quantity_adjusted: $adjustment->quantity_adjusted,
            quantity_after: $adjustment->quantity_after,
            unit_cost: $adjustment->unit_cost,
            total_cost: $adjustment->total_cost,
            reason: $adjustment->reason,
            notes: $adjustment->notes,
            approval_notes: $adjustment->approval_notes,
            adjustment_date: $adjustment->adjustment_date->format('Y-m-d'),
            approved_at: $adjustment->approved_at?->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
