<?php

namespace App\Repositories\Contracts;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\ProductFiltersDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get paginated products with filters.
     */
    public function paginateWithFilters(ProductFiltersDTO $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all active products.
     */
    public function getActive(): Collection;

    /**
     * Find product by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?Product;

    /**
     * Find product by barcode.
     */
    public function findByBarcode(string $barcode): ?Product;

    /**
     * Find product by product code.
     */
    public function findByProductCode(string $productCode): ?Product;

    /**
     * Create a new product.
     */
    public function create(CreateProductDTO $dto): Product;

    /**
     * Update an existing product.
     */
    public function update(int $id, UpdateProductDTO $dto): Product;

    /**
     * Delete a product.
     */
    public function delete(int $id): bool;

    /**
     * Check if product code exists (excluding given ID).
     */
    public function productCodeExists(string $productCode, ?int $excludeId = null): bool;

    /**
     * Check if barcode exists (excluding given ID).
     */
    public function barcodeExists(string $barcode, ?int $excludeId = null): bool;

    /**
     * Search products by term.
     */
    public function search(string $term, int $limit = 10): Collection;

    /**
     * Get products by category.
     */
    public function getByCategory(int $categoryId, bool $activeOnly = true): Collection;

    /**
     * Get products by brand.
     */
    public function getByBrand(string $brand, bool $activeOnly = true): Collection;

    /**
     * Get products by supplier.
     */
    public function getBySupplier(string $supplier, bool $activeOnly = true): Collection;

    /**
     * Get products by price range.
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, bool $activeOnly = true): Collection;

    /**
     * Get products with barcodes.
     */
    public function getWithBarcodes(bool $activeOnly = true): Collection;

    /**
     * Get products without barcodes.
     */
    public function getWithoutBarcodes(bool $activeOnly = true): Collection;

    /**
     * Get low stock products (placeholder for when stock system is implemented).
     */
    public function getLowStockProducts(): Collection;

    /**
     * Get products for dropdown/select options.
     */
    public function getForDropdown(?int $categoryId = null): Collection;

    /**
     * Get products with category information.
     */
    public function getWithCategory(): Collection;

    /**
     * Count products by status.
     */
    public function countByStatus(bool $active = true): int;

    /**
     * Count products by category.
     */
    public function countByCategory(): Collection;

    /**
     * Get products with profit margins.
     */
    public function getWithProfitMargins(): Collection;

    /**
     * Bulk update products.
     */
    public function bulkUpdate(array $updates): int;

    /**
     * Generate unique product code.
     */
    public function generateUniqueProductCode(): string;

    /**
     * Get products by multiple criteria.
     */
    public function getByMultipleCriteria(array $criteria): Collection;

    /**
     * Get products with their current stock (placeholder).
     */
    public function getWithStock(): Collection;

    /**
     * Update product status (active/inactive).
     */
    public function updateStatus(int $id, bool $isActive): bool;

    /**
     * Get products that need reordering (placeholder).
     */
    public function getProductsNeedingReorder(): Collection;
}
