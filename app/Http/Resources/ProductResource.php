<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'sku'              => $this->sku,
            'barcode'          => $this->barcode,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'category_id'      => $this->category_id,
            'category'         => $this->when($this->relationLoaded('category') && $this->category, [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'brand'            => $this->brand,
            'unit'             => $this->unit,
            'cost_price'       => $this->cost_price,
            'selling_price'    => $this->selling_price,
            'min_price'        => $this->min_price,
            'reorder_level'    => $this->reorder_level,
            'reorder_quantity' => $this->reorder_quantity,
            'is_active'        => $this->is_active,
            'is_taxable'       => $this->is_taxable,
            'is_trackable'     => $this->is_trackable,
            'image_urls'       => $this->image_urls ?? [],
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),
        ];
    }
}
