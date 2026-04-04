<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->when($this->relationLoaded('roles'), function () {
                return $this->roles->pluck('name');
            }),
            'formatted_roles' => $this->when($this->relationLoaded('roles'), function () {
                return $this->roles->pluck('name')->map(function ($role) {
                    return ucfirst(str_replace('_', ' ', $role));
                });
            }),
            'permissions' => $this->when($this->relationLoaded('permissions'), function () {
                return $this->permissions->pluck('name');
            }),
            'email_verified_at' => $this->whenNotNull($this->email_verified_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_admin' => $this->when($this->relationLoaded('roles'), function () {
                return $this->roles->contains('name', 'admin');
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
