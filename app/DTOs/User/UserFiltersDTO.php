<?php

namespace App\DTOs\User;

use App\DTOs\Base\BaseDataTransferObject;

class UserFiltersDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $role = null,
        public readonly ?int $perPage = 10,
        public readonly ?string $sortBy = 'created_at',
        public readonly ?string $sortDirection = 'desc'
    ) {
        $this->validate();
    }

    protected function validate(): void
    {
        $data = $this->toArray();
        $this->performValidation($data);
    }

    protected function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sortBy' => ['nullable', 'string', 'in:name,email,created_at,updated_at'],
            'sortDirection' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    protected function messages(): array
    {
        return [
            'search.max' => 'Search term may not be greater than 255 characters.',
            'role.exists' => 'The selected role is invalid.',
            'perPage.min' => 'Items per page must be at least 1.',
            'perPage.max' => 'Items per page may not be greater than 100.',
            'sortBy.in' => 'Sort by field is invalid.',
            'sortDirection.in' => 'Sort direction must be asc or desc.',
        ];
    }

    /**
     * Check if search filter is active.
     */
    public function hasSearch(): bool
    {
        return !empty($this->search);
    }

    /**
     * Check if role filter is active.
     */
    public function hasRoleFilter(): bool
    {
        return !empty($this->role);
    }

    /**
     * Get filters as array for repository.
     */
    public function getFilters(): array
    {
        return array_filter([
            'search' => $this->search,
            'role' => $this->role,
        ]);
    }

    /**
     * Get pagination settings.
     */
    public function getPaginationSettings(): array
    {
        return [
            'perPage' => $this->perPage,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];
    }
}
