<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'name'           => $this->name,
            'address'        => $this->address,
            'city'           => $this->city,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'manager_id'     => $this->manager_id,
            'manager'        => $this->when($this->relationLoaded('manager') && $this->manager, [
                'id'   => $this->manager?->id,
                'name' => $this->manager?->name,
            ]),
            'is_active'      => $this->is_active,
            'is_main_branch' => $this->is_main_branch,
            'timezone'       => $this->timezone,
            'currency'       => $this->currency,
            'tax_rate'       => $this->tax_rate,
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
