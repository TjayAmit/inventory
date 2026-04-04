<?php

namespace App\Repositories\Contracts;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get paginated categories with optional relationships.
     */
    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator;

    /**
     * Get all active categories ordered by sort order and name.
     */
    public function getActiveOrdered(): Collection;

    /**
     * Get root categories (no parent) ordered by sort order and name.
     */
    public function getRootCategories(): Collection;

    /**
     * Get child categories for a given parent.
     */
    public function getChildCategories(int $parentId): Collection;

    /**
     * Find category by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?Category;

    /**
     * Find category by name.
     */
    public function findByName(string $name): ?Category;

    /**
     * Create a new category.
     */
    public function create(CreateCategoryDTO $dto): Category;

    /**
     * Update an existing category.
     */
    public function update(int $id, UpdateCategoryDTO $dto): Category;

    /**
     * Delete a category.
     */
    public function delete(int $id): bool;

    /**
     * Check if category name exists (excluding given ID).
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;

    /**
     * Get categories with product counts.
     */
    public function getWithProductCounts(): Collection;

    /**
     * Get category tree structure.
     */
    public function getCategoryTree(): Collection;

    /**
     * Get descendants of a category.
     */
    public function getDescendants(int $categoryId): Collection;

    /**
     * Check if category has children.
     */
    public function hasChildren(int $categoryId): bool;

    /**
     * Check if category has products.
     */
    public function hasProducts(int $categoryId): bool;

    /**
     * Update sort order for categories.
     */
    public function updateSortOrder(array $categoryIds): bool;

    /**
     * Search categories by term.
     */
    public function search(string $term, int $limit = 10): Collection;

    /**
     * Get categories for dropdown/select options.
     */
    public function getForDropdown(?int $excludeId = null): Collection;
}
