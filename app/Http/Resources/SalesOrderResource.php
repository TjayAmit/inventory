<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'branch_id'       => $this->branch_id,
            'branch'          => $this->when($this->relationLoaded('branch') && $this->branch, [
                'id'   => $this->branch?->id,
                'name' => $this->branch?->name,
            ]),
            'cashier_id'      => $this->cashier_id,
            'cashier'         => $this->when($this->relationLoaded('cashier') && $this->cashier, [
                'id'   => $this->cashier?->id,
                'name' => $this->cashier?->name,
            ]),
            'order_date'      => $this->order_date?->toDateString(),
            'order_time'      => $this->order_time,
            'status'          => $this->status,
            'subtotal'        => $this->subtotal,
            'tax_amount'      => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount'    => $this->total_amount,
            'paid_amount'     => $this->paid_amount,
            'change_amount'   => $this->change_amount,
            'payment_status'  => $this->payment_status,
            'payment_method'  => $this->payment_method,
            'notes'           => $this->notes,
            'items'           => SalesItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at->toISOString(),
            'updated_at'      => $this->updated_at->toISOString(),
        ];
    }
}
