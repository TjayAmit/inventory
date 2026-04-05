<?php

namespace App\DTOs\User;

use App\DTOs\Base\BaseDataTransferObject;

class UserResponseDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $roles,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $emailVerifiedAt = null
    ) {
        // Response DTOs don't need validation as they're created from trusted data
    }

    public function validate(): null
    {
        // No validation needed for response DTOs
    }

    protected function rules(): array
    {
        return [];
    }

    /**
     * Create from User model.
     */
    public static function fromModel($user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            roles: $user->roles->pluck('name')->toArray(),
            createdAt: $user->created_at->toISOString(),
            updatedAt: $user->updated_at->toISOString(),
            emailVerifiedAt: $user->email_verified_at?->toISOString(),
        );
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            roles: $data['roles'] ?? [],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            emailVerifiedAt: $data['email_verified_at'] ?? null,
        );
    }

    /**
     * Get formatted roles for display.
     */
    public function getFormattedRoles(): array
    {
        return array_map(function ($role) {
            return ucfirst(str_replace('_', ' ', $role));
        }, $this->roles);
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return in_array('admin', $this->roles);
    }

    /**
     * Check if user has specific role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Convert DTO to array with snake_case keys for API compatibility.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'email_verified_at' => $this->emailVerifiedAt,
        ];
    }
}
