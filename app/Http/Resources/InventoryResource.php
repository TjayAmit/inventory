<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'product_id'          => $this->product_id,
            'product'             => $this->when($this->relationLoaded('product') && $this->product, [
                'id'   => $this->product?->id,
                'name' => $this->product?->name,
                'sku'  => $this->product?->sku,
            ]),
            'branch_id'           => $this->branch_id,
            'branch'              => $this->when($this->relationLoaded('branch') && $this->branch, [
                'id'   => $this->branch?->id,
                'name' => $this->branch?->name,
                'code' => $this->branch?->code,
            ]),
            'quantity_on_hand'    => $this->quantity_on_hand,
            'quantity_reserved'   => $this->quantity_reserved,
            'quantity_available'  => $this->quantity_available,
            'average_cost'        => $this->average_cost,
            'last_count_date'     => $this->last_count_date?->toDateString(),
            'last_received_date'  => $this->last_received_date?->toDateString(),
            'is_active'           => $this->is_active,
            'created_at'          => $this->created_at->toISOString(),
            'updated_at'          => $this->updated_at->toISOString(),
        ];
    }
}
