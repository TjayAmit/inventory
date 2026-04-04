<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view products
        return true;
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        // All authenticated users can view individual products
        return true;
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        // Only admin, store manager, and warehouse staff can create products
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can update products
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Only admin can delete products
        // Store managers can only delete if product has no sales records
        if (!$user->hasRole(['admin'])) {
            return false;
        }

        // Additional business rule: Cannot delete product with sales records
        if ($product->saleItems()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        // Only admin can restore deleted products
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can permanently delete the product.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only admin can permanently delete products
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can manage product pricing.
     */
    public function managePricing(User $user, Product $product): bool
    {
        // Only admin and store manager can manage pricing
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can toggle product status.
     */
    public function toggleStatus(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can toggle product status
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can update product inventory.
     */
    public function updateInventory(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can update inventory
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can manage product barcode.
     */
    public function manageBarcode(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can manage barcodes
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can assign product to category.
     */
    public function assignCategory(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can assign categories
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can view product statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // Admin and store manager can view statistics
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can export products.
     */
    public function export(User $user): bool
    {
        // Admin and store manager can export products
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can import products.
     */
    public function import(User $user): bool
    {
        // Only admin and store manager can import products
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can bulk update products.
     */
    public function bulkUpdate(User $user): bool
    {
        // Only admin and store manager can bulk update products
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view product audit log.
     */
    public function viewAuditLog(User $user): bool
    {
        // Only admin can view audit log
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can access product API endpoints.
     */
    public function accessApi(User $user): bool
    {
        // All authenticated users can access product API for reading
        // Admin, store manager, and warehouse staff can access for writing
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can scan product barcode.
     */
    public function scanBarcode(User $user): bool
    {
        // All authenticated users can scan barcodes (for POS, inventory, etc.)
        return true;
    }

    /**
     * Determine whether the user can search products.
     */
    public function search(User $user): bool
    {
        // All authenticated users can search products
        return true;
    }

    /**
     * Determine whether the user can view product stock levels.
     */
    public function viewStock(User $user, Product $product): bool
    {
        // All authenticated users can view stock levels
        return true;
    }

    /**
     * Determine whether the user can adjust product stock.
     */
    public function adjustStock(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can adjust stock
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can manage product suppliers.
     */
    public function manageSuppliers(User $user, Product $product): bool
    {
        // Only admin and store manager can manage suppliers
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view product profit margins.
     */
    public function viewProfitMargins(User $user): bool
    {
        // Only admin and store manager can view profit margins
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can manage product discounts.
     */
    public function manageDiscounts(User $user, Product $product): bool
    {
        // Only admin and store manager can manage discounts
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Additional abilities for fine-grained permissions
     */

    /**
     * Determine whether the user can deactivate product.
     */
    public function deactivate(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can deactivate products
        if (!$user->hasRole(['admin', 'store_manager', 'warehouse_staff'])) {
            return false;
        }

        // Cannot deactivate if product has active stock (when stock system is implemented)
        // This is a placeholder for future implementation
        return true;
    }

    /**
     * Determine whether the user can reactivate product.
     */
    public function reactivate(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can reactivate products
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can generate barcode for product.
     */
    public function generateBarcode(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can generate barcodes
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can update product cost price.
     */
    public function updateCostPrice(User $user, Product $product): bool
    {
        // Only admin and store manager can update cost price
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can update product selling price.
     */
    public function updateSellingPrice(User $user, Product $product): bool
    {
        // Only admin and store manager can update selling price
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can manage product reorder points.
     */
    public function manageReorderPoints(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can manage reorder points
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can view product sales history.
     */
    public function viewSalesHistory(User $user, Product $product): bool
    {
        // Admin and store manager can view sales history
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view deleted products.
     */
    public function viewTrashed(User $user): bool
    {
        // Only admin can view deleted products
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can duplicate product.
     */
    public function duplicate(User $user, Product $product): bool
    {
        // Only admin, store manager, and warehouse staff can duplicate products
        return $user->hasRole(['admin', 'store_manager', 'warehouse_staff']);
    }

    /**
     * Determine whether the user can manage product variants.
     */
    public function manageVariants(User $user, Product $product): bool
    {
        // Only admin and store manager can manage variants
        return $user->hasRole(['admin', 'store_manager']);
    }

    /**
     * Determine whether the user can view product analytics.
     */
    public function viewAnalytics(User $user, Product $product): bool
    {
        // Only admin and store manager can view analytics
        return $user->hasRole(['admin', 'store_manager']);
    }
}
