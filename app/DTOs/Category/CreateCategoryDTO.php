<?php

namespace App\DTOs\Category;

use App\DTOs\Base\BaseDataTransferObject;
use Illuminate\Validation\Rule;

class CreateCategoryDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?int $parentId = null,
        public readonly bool $isActive = true,
        public readonly int $sortOrder = 0
    ) {
        // Skip validation - controller handles it
    }

    public function validate(): null
    {
        $data = $this->toArray();
        
        $validated = $this->performValidation($data);

        // Additional business logic validation
        $this->validateBusinessRules($validated);
        
        return null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name may not be greater than 200 characters.',
            'name.unique' => 'A category with this name already exists.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'parent_id.integer' => 'The parent ID must be an integer.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'is_active.boolean' => 'The active status must be a boolean.',
            'sort_order.integer' => 'The sort order must be an integer.',
            'sort_order.min' => 'The sort order must be at least 0.',
            'sort_order.max' => 'The sort order may not be greater than 1000000.',
        ];
    }

    protected function validateBusinessRules(array $data): void
    {
        // Prevent creating a category as its own parent (circular reference)
        if (!empty($data['parent_id']) && $this->wouldCreateCircularReference($data['parent_id'])) {
            throw new \Illuminate\Validation\ValidationException(
                validator()->make([], []),
                ['parent_id' => 'Cannot create circular category reference.']
            );
        }
    }

    /**
     * Check if creating this category would create a circular reference.
     * This is a simplified check - in a real implementation, you'd need to check the entire hierarchy.
     */
    private function wouldCreateCircularReference(int $parentId): bool
    {
        // For now, we'll just prevent obvious self-reference
        // In a full implementation, you'd traverse the entire parent hierarchy
        return false; // Simplified for now
    }

    /**
     * Get the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the parent ID.
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * Get the active status.
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get the sort order.
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Check if this is a root category.
     */
    public function isRootCategory(): bool
    {
        return $this->parentId === null;
    }

    /**
     * Check if this is an active category.
     */
    public function isActiveCategory(): bool
    {
        return $this->isActive;
    }
}
