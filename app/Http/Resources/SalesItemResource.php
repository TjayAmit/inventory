<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'sales_order_id'      => $this->sales_order_id,
            'product_id'          => $this->product_id,
            'product'             => $this->when($this->relationLoaded('product') && $this->product, [
                'id'            => $this->product?->id,
                'name'          => $this->product?->name,
                'sku'           => $this->product?->sku,
                'selling_price' => $this->product?->selling_price,
            ]),
            'inventory_batch_id'  => $this->inventory_batch_id,
            'quantity'            => $this->quantity,
            'unit_price'          => $this->unit_price,
            'unit_cost'           => $this->unit_cost,
            'discount_amount'     => $this->discount_amount,
            'tax_amount'          => $this->tax_amount,
            'total_price'         => $this->total_price,
            'total_cost'          => $this->total_cost,
            'profit'              => $this->profit,
            'created_at'          => $this->created_at->toISOString(),
            'updated_at'          => $this->updated_at->toISOString(),
        ];
    }
}
