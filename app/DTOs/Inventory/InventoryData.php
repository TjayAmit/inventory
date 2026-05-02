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
        public readonly int $reorder_point = 0,
        public readonly ?int $max_stock = null,
        public readonly ?int $min_stock = null,
        public readonly float $average_cost = 0.0000,
        public readonly float $total_cost = 0.00,
        public readonly ?string $last_count_date = null,
        public readonly ?string $last_received_date = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: $request->validated('product_id'),
            branch_id: $request->validated('branch_id'),
            quantity_on_hand: $request->validated('quantity_on_hand', 0),
            quantity_reserved: $request->validated('quantity_reserved', 0),
            quantity_available: $request->validated('quantity_available', 0),
            reorder_point: $request->validated('reorder_point', 0),
            max_stock: $request->validated('max_stock'),
            min_stock: $request->validated('min_stock'),
            average_cost: $request->validated('average_cost', 0.0000),
            total_cost: $request->validated('total_cost', 0.00),
            last_count_date: $request->validated('last_count_date'),
            last_received_date: $request->validated('last_received_date'),
            is_active: $request->validated('is_active', true),
        );
    }

    public static function fromModel(Inventory $inventory): self
    {
        return new self(
            product_id: $inventory->product_id,
            branch_id: $inventory->branch_id,
            quantity_on_hand: $inventory->quantity_on_hand,
            quantity_reserved: $inventory->quantity_reserved,
            quantity_available: $inventory->quantity_available,
            reorder_point: $inventory->reorder_point,
            max_stock: $inventory->max_stock,
            min_stock: $inventory->min_stock,
            average_cost: $inventory->average_cost,
            total_cost: $inventory->total_cost,
            last_count_date: $inventory->last_count_date?->format('Y-m-d'),
            last_received_date: $inventory->last_received_date?->format('Y-m-d'),
            is_active: $inventory->is_active,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
