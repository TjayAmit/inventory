<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any categories.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view categories
        return true;
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): bool
    {
        // All authenticated users can view individual categories
        return true;
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        // Only admin and store manager can create categories
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): bool
    {
        // Only admin and store manager can update categories
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): bool
    {
        // Only admin can delete categories
        // Store managers can only delete if they have permission and category has no products
        if (!$user->hasRole(['admin'])) {
            return false;
        }

        // Additional business rule: Cannot delete category with children
        if ($category->children()->exists()) {
            return false;
        }

        // Additional business rule: Cannot delete category with products
        if ($category->products()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the category.
     */
    public function restore(User $user, Category $category): bool
    {
        // Only admin can restore deleted categories
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can permanently delete the category.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        // Only admin can permanently delete categories
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can manage category hierarchy.
     */
    public function manageHierarchy(User $user): bool
    {
        // Only admin and store manager can manage category hierarchy
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can move categories.
     */
    public function move(User $user, Category $category): bool
    {
        // Only admin and store manager can move categories
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can toggle category status.
     */
    public function toggleStatus(User $user, Category $category): bool
    {
        // Only admin and store manager can toggle category status
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can update category sort order.
     */
    public function updateSortOrder(User $user): bool
    {
        // Only admin and store manager can update sort order
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view category statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // Admin and store manager can view statistics
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can export categories.
     */
    public function export(User $user): bool
    {
        // Admin and store manager can export categories
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can import categories.
     */
    public function import(User $user): bool
    {
        // Only admin can import categories
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can view category products.
     */
    public function viewProducts(User $user, Category $category): bool
    {
        // All authenticated users can view category products
        return true;
    }

    /**
     * Determine whether the user can manage category products.
     */
    public function manageProducts(User $user, Category $category): bool
    {
        // Only admin and store manager can manage category products
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can assign products to category.
     */
    public function assignProducts(User $user, Category $category): bool
    {
        // Only admin and store manager can assign products to category
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can bulk update categories.
     */
    public function bulkUpdate(User $user): bool
    {
        // Only admin can bulk update categories
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can view category audit log.
     */
    public function viewAuditLog(User $user): bool
    {
        // Only admin can view audit log
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can access category API endpoints.
     */
    public function accessApi(User $user): bool
    {
        // All authenticated users can access category API for reading
        // Admin and store manager can access for writing
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Additional abilities for fine-grained permissions
     */

    /**
     * Determine whether the user can create subcategories under this category.
     */
    public function createSubcategory(User $user, Category $category): bool
    {
        // Only admin and store manager can create subcategories
        // and only if parent category is active
        if (!$user->hasRole(['admin', 'store_manager'])) {
            return false;
        }

        return $category->is_active;
    }

    /**
     * Determine whether the user can move category to a new parent.
     */
    public function changeParent(User $user, Category $category): bool
    {
        // Only admin and store manager can change parent
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can deactivate category.
     */
    public function deactivate(User $user, Category $category): bool
    {
        // Only admin and store manager can deactivate categories
        if (!$user->hasRole(['admin', 'store_manager'])) {
            return false;
        }

        // Cannot deactivate if it has active products
        if ($category->products()->where('is_active', true)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reactivate category.
     */
    public function reactivate(User $user, Category $category): bool
    {
        // Only admin and store manager can reactivate categories
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view deleted categories.
     */
    public function viewTrashed(User $user): bool
    {
        // Only admin can view deleted categories
        return $user->hasRole(['admin']);
    }
}
