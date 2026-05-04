<?php

namespace App\DTOs\Inventory;

use Illuminate\Http\Request;

class InventoryData
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $branch_id,
        public readonly int $quantity_on_hand = 0,
        public readonly int $quantity_reserved = 0,
        public readonly int $quantity_available = 0,
        public readonly float $average_cost = 0.0000,
        public readonly ?string $last_count_date = null,
        public readonly ?string $last_received_date = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: $request->validated('product_id'),
            branch_id: $request->validated('branch_id'),
            quantity_on_hand: $request->validated('quantity_on_hand'),
            quantity_reserved: $request->validated('quantity_reserved'),
            quantity_available: $request->validated('quantity_available'),
            average_cost: $request->validated('average_cost'),
            last_count_date: $request->validated('last_count_date'),
            last_received_date: $request->validated('last_received_date'),
            is_active: $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
