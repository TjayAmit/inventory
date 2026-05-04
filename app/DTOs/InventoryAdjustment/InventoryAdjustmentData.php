<?php

namespace App\DTOs\InventoryAdjustment;

use Illuminate\Http\Request;

class InventoryAdjustmentData
{
    public function __construct(
        public readonly int $inventory_id,
        public readonly int $adjusted_by,
        public readonly ?int $approved_by = null,
        public readonly string $reason_code,
        public readonly int $quantity_before,
        public readonly int $quantity_change,
        public readonly int $quantity_after,
        public readonly ?string $notes = null,
        public readonly ?string $approved_at = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            inventory_id: $request->validated('inventory_id'),
            adjusted_by: $request->validated('adjusted_by'),
            approved_by: $request->validated('approved_by'),
            reason_code: $request->validated('reason_code'),
            quantity_before: $request->validated('quantity_before'),
            quantity_change: $request->validated('quantity_change'),
            quantity_after: $request->validated('quantity_after'),
            notes: $request->validated('notes'),
            approved_at: $request->validated('approved_at'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
