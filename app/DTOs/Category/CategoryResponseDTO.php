<?php

namespace App\DTOs\Category;

use App\DTOs\Base\BaseDataTransferObject;

class CategoryResponseDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?int $parentId,
        public readonly ?string $parentName,
        public readonly bool $isActive,
        public readonly int $sortOrder,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?array $children = null,
        public readonly ?int $productsCount = null,
        public readonly ?string $fullPath = null
    ) {
        // No validation needed for response DTO
    }

    protected function validate(): void
    {
        // Response DTOs don't need validation
    }

    protected function rules(): array
    {
        return [];
    }

    /**
     * Create from a Category model.
     */
    public static function fromModel($category, bool $includeChildren = false, bool $includeProductsCount = false): self
    {
        $children = null;
        $productsCount = null;
        $fullPath = null;

        if ($includeChildren && $category->relationLoaded('children')) {
            $children = $category->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'isActive' => $child->is_active,
                    'sortOrder' => $child->sort_order,
                ];
            })->toArray();
        }

        if ($includeProductsCount) {
            $productsCount = $category->products()->count();
        }

        if ($category->relationLoaded('parent') && $category->parent) {
            $fullPath = $category->full_path;
        } elseif (!$category->parent_id) {
            $fullPath = $category->name;
        }

        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            parentId: $category->parent_id,
            parentName: $category->parent?->name,
            isActive: $category->is_active,
            sortOrder: $category->sort_order,
            createdAt: $category->created_at->toISOString(),
            updatedAt: $category->updated_at->toISOString(),
            children: $children,
            productsCount: $productsCount,
            fullPath: $fullPath
        );
    }

    /**
     * Create a simple version for API responses.
     */
    public static function simple($category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            parentId: $category->parent_id,
            parentName: $category->parent?->name,
            isActive: $category->is_active,
            sortOrder: $category->sort_order,
            createdAt: $category->created_at->toISOString(),
            updatedAt: $category->updated_at->toISOString()
        );
    }

    /**
     * Get the category ID.
     */
    public function getId(): int
    {
        return $this->id;
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
     * Get the parent name.
     */
    public function getParentName(): ?string
    {
        return $this->parentName;
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
     * Get the creation date.
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Get the update date.
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Get the children categories.
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * Get the products count.
     */
    public function getProductsCount(): ?int
    {
        return $this->productsCount;
    }

    /**
     * Get the full path.
     */
    public function getFullPath(): ?string
    {
        return $this->fullPath;
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

    /**
     * Check if this category has children.
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Check if this category has products.
     */
    public function hasProducts(): bool
    {
        return $this->productsCount !== null && $this->productsCount > 0;
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parentId' => $this->parentId,
            'parentName' => $this->parentName,
            'isActive' => $this->isActive,
            'sortOrder' => $this->sortOrder,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'children' => $this->children,
            'productsCount' => $this->productsCount,
            'fullPath' => $this->fullPath,
            'isRoot' => $this->isRootCategory(),
            'hasChildren' => $this->hasChildren(),
            'hasProducts' => $this->hasProducts(),
        ];
    }
}
