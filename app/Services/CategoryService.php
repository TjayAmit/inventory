<?php

namespace App\Services;

use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get paginated categories.
     */
    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage, ['parent', 'children']);
    }

    /**
     * Get all active categories ordered by sort order and name.
     */
    public function getActiveCategories(): Collection
    {
        $categories = $this->categoryRepository->getActiveOrdered();
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, true, true);
        });
    }

    /**
     * Get category tree structure.
     */
    public function getCategoryTree(): Collection
    {
        $categories = $this->categoryRepository->getCategoryTree();
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, true, false);
        });
    }

    /**
     * Get root categories (no parent).
     */
    public function getRootCategories(): Collection
    {
        $categories = $this->categoryRepository->getRootCategories();
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, false, false);
        });
    }

    /**
     * Get child categories for a given parent.
     */
    public function getChildCategories(int $parentId): Collection
    {
        $categories = $this->categoryRepository->getChildCategories($parentId);
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, false, false);
        });
    }

    /**
     * Find category by ID.
     */
    public function getCategoryById(int $id): ?CategoryResponseDTO
    {
        $category = $this->categoryRepository->findById($id, ['parent', 'children']);
        
        if (!$category) {
            return null;
        }
        
        return CategoryResponseDTO::fromModel($category, true, true);
    }

    /**
     * Find category by name.
     */
    public function getCategoryByName(string $name): ?CategoryResponseDTO
    {
        $category = $this->categoryRepository->findByName($name);
        
        if (!$category) {
            return null;
        }
        
        return CategoryResponseDTO::fromModel($category, false, false);
    }

    /**
     * Create a new category.
     */
    public function createCategory(CreateCategoryDTO $dto): CategoryResponseDTO
    {
        // Additional business logic validations
        $this->validateCategoryCreation($dto);

        try {
            DB::beginTransaction();

            $category = $this->categoryRepository->create($dto);

            DB::commit();

            return CategoryResponseDTO::fromModel($category, true, false);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw new \RuntimeException('Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(int $id, UpdateCategoryDTO $dto): CategoryResponseDTO
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        // Additional business logic validations
        $this->validateCategoryUpdate($category, $dto);

        try {
            DB::beginTransaction();

            $updatedCategory = $this->categoryRepository->update($id, $dto);

            DB::commit();

            return CategoryResponseDTO::fromModel($updatedCategory, true, true);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw new \RuntimeException('Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->findById($id, ['children', 'products']);
        
        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        // Business rule: Cannot delete category with children
        if ($this->categoryRepository->hasChildren($id)) {
            throw new \InvalidArgumentException('Cannot delete category that has subcategories. Please delete or move subcategories first.');
        }

        // Business rule: Cannot delete category with products
        if ($this->categoryRepository->hasProducts($id)) {
            throw new \InvalidArgumentException('Cannot delete category that has products. Please move or delete products first.');
        }

        try {
            DB::beginTransaction();

            $result = $this->categoryRepository->delete($id);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Search categories.
     */
    public function searchCategories(string $term, int $limit = 10): Collection
    {
        $categories = $this->categoryRepository->search($term, $limit);
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, false, false);
        });
    }

    /**
     * Get categories for dropdown/select.
     */
    public function getCategoriesForDropdown(?int $excludeId = null): Collection
    {
        return $this->categoryRepository->getForDropdown($excludeId);
    }

    /**
     * Get categories with product counts.
     */
    public function getCategoriesWithProductCounts(): Collection
    {
        $categories = $this->categoryRepository->getWithProductCounts();
        
        return $categories->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, false, true);
        });
    }

    /**
     * Update sort order for categories.
     */
    public function updateCategorySortOrder(array $categoryIds): bool
    {
        try {
            DB::beginTransaction();

            $result = $this->categoryRepository->updateSortOrder($categoryIds);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Failed to update sort order: ' . $e->getMessage());
        }
    }

    /**
     * Get descendants of a category.
     */
    public function getCategoryDescendants(int $categoryId): Collection
    {
        $descendants = $this->categoryRepository->getDescendants($categoryId);
        
        return $descendants->map(function ($category) {
            return CategoryResponseDTO::fromModel($category, false, false);
        });
    }

    /**
     * Move category to new parent.
     */
    public function moveCategory(int $categoryId, ?int $newParentId): CategoryResponseDTO
    {
        $category = $this->categoryRepository->findById($categoryId);
        
        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        // Prevent moving category to its own descendant
        if ($newParentId && $this->isDescendant($newParentId, $categoryId)) {
            throw new \InvalidArgumentException('Cannot move category to its own descendant.');
        }

        $dto = new UpdateCategoryDTO(
            name: $category->name,
            description: $category->description,
            parentId: $newParentId,
            isActive: $category->is_active,
            sortOrder: $category->sort_order,
            categoryId: $categoryId
        );

        return $this->updateCategory($categoryId, $dto);
    }

    /**
     * Toggle category active status.
     */
    public function toggleCategoryStatus(int $id): CategoryResponseDTO
    {
        $category = $this->categoryRepository->findById($id, ['products']);
        
        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        $newStatus = !$category->is_active;

        // Business rule: Cannot deactivate category with active products
        if (!$newStatus && $this->categoryRepository->hasProducts($id)) {
            throw new \InvalidArgumentException('Cannot deactivate category that has products. Please move or deactivate products first.');
        }

        $dto = new UpdateCategoryDTO(
            name: $category->name,
            description: $category->description,
            parentId: $category->parent_id,
            isActive: $newStatus,
            sortOrder: $category->sort_order,
            categoryId: $id
        );

        return $this->updateCategory($id, $dto);
    }

    /**
     * Get category statistics.
     */
    public function getCategoryStatistics(): array
    {
        $totalCategories = $this->categoryRepository->getModel()->count();
        $activeCategories = $this->categoryRepository->countByStatus(true);
        $inactiveCategories = $this->categoryRepository->countByStatus(false);
        $rootCategories = $this->categoryRepository->getRootCategories()->count();

        return [
            'total' => $totalCategories,
            'active' => $activeCategories,
            'inactive' => $inactiveCategories,
            'root' => $rootCategories,
            'active_percentage' => $totalCategories > 0 ? round(($activeCategories / $totalCategories) * 100, 2) : 0,
        ];
    }

    /**
     * Validate category creation business rules.
     */
    private function validateCategoryCreation(CreateCategoryDTO $dto): void
    {
        // Check if name already exists
        if ($this->categoryRepository->nameExists($dto->getName())) {
            throw ValidationException::withMessages([
                'name' => 'A category with this name already exists.'
            ]);
        }

        // Validate parent category if specified
        if ($dto->getParentId()) {
            $parent = $this->categoryRepository->findById($dto->getParentId());
            if (!$parent) {
                throw ValidationException::withMessages([
                    'parentId' => 'The selected parent category does not exist.'
                ]);
            }

            if (!$parent->is_active) {
                throw ValidationException::withMessages([
                    'parentId' => 'Cannot assign to an inactive parent category.'
                ]);
            }
        }
    }

    /**
     * Validate category update business rules.
     */
    private function validateCategoryUpdate(Category $category, UpdateCategoryDTO $dto): void
    {
        // Check if name already exists (excluding current category)
        if ($this->categoryRepository->nameExists($dto->getName(), $category->id)) {
            throw ValidationException::withMessages([
                'name' => 'A category with this name already exists.'
            ]);
        }

        // Validate parent category if being changed
        if ($dto->getParentId() !== null && $dto->getParentId() != $category->parent_id) {
            // Prevent setting self as parent
            if ($dto->getParentId() == $category->id) {
                throw ValidationException::withMessages([
                    'parentId' => 'A category cannot be its own parent.'
                ]);
            }

            // Prevent circular reference
            if ($this->wouldCreateCircularReference($category->id, $dto->getParentId())) {
                throw ValidationException::withMessages([
                    'parentId' => 'This would create a circular reference in the category hierarchy.'
                ]);
            }

            $parent = $this->categoryRepository->findById($dto->getParentId());
            if (!$parent) {
                throw ValidationException::withMessages([
                    'parentId' => 'The selected parent category does not exist.'
                ]);
            }

            if (!$parent->is_active) {
                throw ValidationException::withMessages([
                    'parentId' => 'Cannot assign to an inactive parent category.'
                ]);
            }
        }

        // Validate deactivation if category has products
        if ($dto->getIsActive() === false && $this->categoryRepository->hasProducts($category->id)) {
            throw ValidationException::withMessages([
                'isActive' => 'Cannot deactivate category that has products. Please move or deactivate products first.'
            ]);
        }
    }

    /**
     * Check if moving category would create circular reference.
     */
    private function wouldCreateCircularReference(int $categoryId, int $newParentId): bool
    {
        $descendants = $this->categoryRepository->getDescendants($categoryId);
        
        return $descendants->contains('id', $newParentId);
    }

    /**
     * Check if a category is a descendant of another.
     */
    private function isDescendant(int $potentialDescendantId, int $ancestorId): bool
    {
        $descendants = $this->categoryRepository->getDescendants($ancestorId);
        
        return $descendants->contains('id', $potentialDescendantId);
    }

    /**
     * Get the repository instance.
     */
    public function getRepository(): CategoryRepositoryInterface
    {
        return $this->categoryRepository;
    }
}
