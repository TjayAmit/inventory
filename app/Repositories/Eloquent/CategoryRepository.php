<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected Category $model;

    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    /**
     * Get paginated categories with optional relationships.
     */
    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        return $query->ordered()
            ->paginate($perPage);
    }

    /**
     * Get all active categories ordered by sort order and name.
     */
    public function getActiveOrdered(): Collection
    {
        return $this->model->active()
            ->ordered()
            ->get();
    }

    /**
     * Get root categories (no parent) ordered by sort order and name.
     */
    public function getRootCategories(): Collection
    {
        return $this->model->root()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get child categories for a given parent.
     */
    public function getChildCategories(int $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Find category by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?Category
    {
        return $this->model->with($relations)
            ->find($id);
    }

    /**
     * Find category by name.
     */
    public function findByName(string $name): ?Category
    {
        return $this->model->where('name', $name)
            ->first();
    }

    /**
     * Create a new category.
     */
    public function create(CreateCategoryDTO $dto): Category
    {
        $category = $this->model->create([
            'name' => $dto->getName(),
            'description' => $dto->getDescription(),
            'parent_id' => $dto->getParentId(),
            'is_active' => $dto->getIsActive(),
            'sort_order' => $dto->getSortOrder(),
        ]);

        // Load relationships if needed
        if ($dto->getParentId()) {
            $category->load('parent');
        }

        return $category;
    }

    /**
     * Update an existing category.
     */
    public function update(int $id, UpdateCategoryDTO $dto): Category
    {
        $category = $this->model->findOrFail($id);

        $updateData = [
            'name' => $dto->getName(),
            'description' => $dto->getDescription(),
        ];

        // Only update fields that are being changed
        if ($dto->getParentId() !== null) {
            $updateData['parent_id'] = $dto->getParentId();
        }

        if ($dto->getIsActive() !== null) {
            $updateData['is_active'] = $dto->getIsActive();
        }

        if ($dto->getSortOrder() !== null) {
            $updateData['sort_order'] = $dto->getSortOrder();
        }

        $category->update($updateData);

        // Load relationships
        $category->load('parent', 'children');

        return $category;
    }

    /**
     * Delete a category.
     */
    public function delete(int $id): bool
    {
        $category = $this->model->findOrFail($id);

        // Prevent deletion if category has children
        if ($this->hasChildren($id)) {
            throw new \InvalidArgumentException('Cannot delete category that has subcategories.');
        }

        // Prevent deletion if category has products
        if ($this->hasProducts($id)) {
            throw new \InvalidArgumentException('Cannot delete category that has products.');
        }

        return $category->delete();
    }

    /**
     * Check if category name exists (excluding given ID).
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get categories with product counts.
     */
    public function getWithProductCounts(): Collection
    {
        return $this->model->withCount('products')
            ->ordered()
            ->get();
    }

    /**
     * Get category tree structure.
     */
    public function getCategoryTree(): Collection
    {
        $categories = $this->model->with(['children' => function ($query) {
            $query->active()->ordered();
        }])
            ->root()
            ->active()
            ->ordered()
            ->get();

        return $categories;
    }

    /**
     * Get descendants of a category.
     */
    public function getDescendants(int $categoryId): Collection
    {
        $category = $this->model->with('descendants')->find($categoryId);

        if (!$category) {
            return collect();
        }

        return $this->collectDescendants($category->children);
    }

    /**
     * Recursively collect descendants.
     */
    private function collectDescendants(Collection $children): Collection
    {
        $descendants = collect();

        foreach ($children as $child) {
            $descendants->push($child);

            if ($child->children->isNotEmpty()) {
                $descendants = $descendants->merge($this->collectDescendants($child->children));
            }
        }

        return $descendants;
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(int $categoryId): bool
    {
        return $this->model->where('parent_id', $categoryId)
            ->exists();
    }

    /**
     * Check if category has products.
     */
    public function hasProducts(int $categoryId): bool
    {
        return $this->model->find($categoryId)
            ->products()
            ->exists();
    }

    /**
     * Update sort order for categories.
     */
    public function updateSortOrder(array $categoryIds): bool
    {
        try {
            \DB::transaction(function () use ($categoryIds) {
                foreach ($categoryIds as $index => $categoryId) {
                    $this->model->where('id', $categoryId)
                        ->update(['sort_order' => $index]);
                }
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Search categories by term.
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return $this->model->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->active()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Get categories for dropdown/select options.
     */
    public function getForDropdown(?int $excludeId = null): Collection
    {
        $query = $this->model->active()->ordered();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get(['id', 'name', 'parent_id']);
    }

    /**
     * Get the model instance.
     */
    public function getModel(): Category
    {
        return $this->model;
    }

    /**
     * Begin a new query.
     */
    public function query()
    {
        return $this->model->newQuery();
    }

    /**
     * Get categories by parent ID.
     */
    public function getByParentId(int $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Count categories by status.
     */
    public function countByStatus(bool $active = true): int
    {
        return $this->model->where('is_active', $active)->count();
    }

    /**
     * Get categories with their full path.
     */
    public function getWithFullPath(): Collection
    {
        $categories = $this->model->active()->ordered()->get();

        return $categories->map(function ($category) {
            $category->full_path = $category->full_path;
            return $category;
        });
    }
}
