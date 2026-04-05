<?php

namespace App\DTOs\Category;

use App\DTOs\Base\BaseDataTransferObject;
use Illuminate\Validation\Rule;

class UpdateCategoryDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly int $categoryId, // The ID of category being updated
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?int $parentId = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null
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
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($this->categoryId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$this->categoryId])],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name may not be greater than 100 characters.',
            'name.unique' => 'A category with this name already exists.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'parent_id.not_in' => 'A category cannot be its own parent.',
            'sort_order.min' => 'The sort order must be at least 0.',
            'sort_order.max' => 'The sort order may not be greater than 9999.',
            'category_id.required' => 'Category ID is required.',
            'category_id.exists' => 'The category being updated does not exist.',
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

        // Additional business rule: Cannot deactivate a category that has active products
        if (isset($data['is_active']) && $data['is_active'] === false && $this->hasActiveProducts()) {
            throw new \Illuminate\Validation\ValidationException(
                validator()->make([], []),
                ['is_active' => 'Cannot deactivate a category that has active products.']
            );
        }
    }

    /**
     * Check if updating this category would create a circular reference.
     */
    private function wouldCreateCircularReference(int $parentId): bool
    {
        // Check if the parent would be a descendant of this category
        // This is a simplified check - in a real implementation, you'd need to check the entire hierarchy
        return $parentId === $this->categoryId;
    }

    /**
     * Check if this category has active products.
     * This is a simplified check - in a real implementation, you'd query the database.
     */
    private function hasActiveProducts(): bool
    {
        // For now, return false to allow deactivation
        // In a full implementation, you'd check: Product::where('category_id', $this->categoryId)->active()->exists()
        return false;
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
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Get the sort order.
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * Get the category ID.
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * Check if this is a root category.
     */
    public function isRootCategory(): bool
    {
        return $this->parentId === null;
    }

    /**
     * Check if the active status is being changed.
     */
    public function isChangingActiveStatus(): bool
    {
        return $this->isActive !== null;
    }

    /**
     * Check if the parent is being changed.
     */
    public function isChangingParent(): bool
    {
        return $this->parentId !== null;
    }

    /**
     * Check if the sort order is being changed.
     */
    public function isChangingSortOrder(): bool
    {
        return $this->sortOrder !== null;
    }
}
