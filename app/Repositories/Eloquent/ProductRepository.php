<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\ProductFiltersDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    protected Product $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    /**
     * Get paginated products with filters.
     */
    public function paginateWithFilters(ProductFiltersDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['category']);

        // Apply filters
        if ($filters->hasSearchFilter()) {
            $query->search($filters->getSearch());
        }

        if ($filters->hasCategoryFilter()) {
            $query->byCategory($filters->getCategoryId());
        }

        if ($filters->hasPriceFilter()) {
            $query->byPriceRange($filters->getMinPrice(), $filters->getMaxPrice());
        }

        if ($filters->isActive !== null) {
            $query->where('is_active', $filters->getIsActive());
        }

        if ($filters->isTaxable !== null) {
            $query->where('is_taxable', $filters->getIsTaxable());
        }

        if ($filters->hasBrandFilter()) {
            $query->where('brand', $filters->getBrand());
        }

        if ($filters->hasSupplierFilter()) {
            $query->where('supplier', $filters->getSupplier());
        }

        if ($filters->hasBarcode !== null) {
            if ($filters->getHasBarcode()) {
                $query->whereNotNull('barcode');
            } else {
                $query->whereNull('barcode');
            }
        }

        if ($filters->hasCostPrice !== null) {
            if ($filters->getHasCostPrice()) {
                $query->whereNotNull('cost_price');
            } else {
                $query->whereNull('cost_price');
            }
        }

        // Apply sorting
        $sortBy = $filters->getSortBy();
        $sortDirection = $filters->getSortDirection();

        if ($filters->isValidSortField() && $filters->isValidSortDirection()) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($filters->getPerPage() ?? $perPage);
    }

    /**
     * Get all active products.
     */
    public function getActive(): Collection
    {
        return $this->model->active()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find product by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?Product
    {
        return $this->model->with($relations)
            ->find($id);
    }

    /**
     * Find product by barcode.
     */
    public function findByBarcode(string $barcode): ?Product
    {
        return $this->model->where('barcode', $barcode)
            ->first();
    }

    /**
     * Find product by product code.
     */
    public function findByProductCode(string $productCode): ?Product
    {
        return $this->model->where('product_code', $productCode)
            ->first();
    }

    /**
     * Create a new product.
     */
    public function create(CreateProductDTO $dto): Product
    {
        $product = $this->model->create([
            'name' => $dto->getName(),
            'product_code' => $dto->getProductCode(),
            'barcode' => $dto->getBarcode(),
            'description' => $dto->getDescription(),
            'price' => $dto->getPrice(),
            'cost_price' => $dto->getCostPrice(),
            'category_id' => $dto->getCategoryId(),
            'is_active' => $dto->getIsActive(),
            'is_taxable' => $dto->getIsTaxable(),
            'unit' => $dto->getUnit(),
            'weight' => $dto->getWeight(),
            'volume' => $dto->getVolume(),
            'brand' => $dto->getBrand(),
            'manufacturer' => $dto->getManufacturer(),
            'supplier' => $dto->getSupplier(),
            'reorder_point' => $dto->getReorderPoint(),
            'max_stock' => $dto->getMaxStock(),
            'notes' => $dto->getNotes(),
        ]);

        // Load relationships
        if ($dto->getCategoryId()) {
            $product->load('category');
        }

        return $product;
    }

    /**
     * Update an existing product.
     */
    public function update(int $id, UpdateProductDTO $dto): Product
    {
        $product = $this->model->findOrFail($id);

        $updateData = [
            'name' => $dto->getName(),
            'product_code' => $dto->getProductCode(),
            'description' => $dto->getDescription(),
            'price' => $dto->getPrice(),
        ];

        // Only update fields that are being changed
        if ($dto->getBarcode() !== null) {
            $updateData['barcode'] = $dto->getBarcode();
        }

        if ($dto->getCostPrice() !== null) {
            $updateData['cost_price'] = $dto->getCostPrice();
        }

        if ($dto->getCategoryId() !== null) {
            $updateData['category_id'] = $dto->getCategoryId();
        }

        if ($dto->getIsActive() !== null) {
            $updateData['is_active'] = $dto->getIsActive();
        }

        if ($dto->getIsTaxable() !== null) {
            $updateData['is_taxable'] = $dto->getIsTaxable();
        }

        if ($dto->getUnit() !== null) {
            $updateData['unit'] = $dto->getUnit();
        }

        if ($dto->getWeight() !== null) {
            $updateData['weight'] = $dto->getWeight();
        }

        if ($dto->getVolume() !== null) {
            $updateData['volume'] = $dto->getVolume();
        }

        if ($dto->getBrand() !== null) {
            $updateData['brand'] = $dto->getBrand();
        }

        if ($dto->getManufacturer() !== null) {
            $updateData['manufacturer'] = $dto->getManufacturer();
        }

        if ($dto->getSupplier() !== null) {
            $updateData['supplier'] = $dto->getSupplier();
        }

        if ($dto->getReorderPoint() !== null) {
            $updateData['reorder_point'] = $dto->getReorderPoint();
        }

        if ($dto->getMaxStock() !== null) {
            $updateData['max_stock'] = $dto->getMaxStock();
        }

        if ($dto->getNotes() !== null) {
            $updateData['notes'] = $dto->getNotes();
        }

        $product->update($updateData);

        // Load relationships
        $product->load('category');

        return $product;
    }

    /**
     * Delete a product.
     */
    public function delete(int $id): bool
    {
        $product = $this->model->findOrFail($id);

        // Prevent deletion if product has stock movements or sales
        // TODO: Implement sales check when SaleItem model exists
        // if ($product->saleItems()->exists()) {
        //     throw new \InvalidArgumentException('Cannot delete product that has sales records.');
        // }

        return $product->delete();
    }

    /**
     * Check if product code exists (excluding given ID).
     */
    public function productCodeExists(string $productCode, ?int $excludeId = null): bool
    {
        $query = $this->model->where('product_code', $productCode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if barcode exists (excluding given ID).
     */
    public function barcodeExists(string $barcode, ?int $excludeId = null): bool
    {
        if (empty($barcode)) {
            return false;
        }

        $query = $this->model->where('barcode', $barcode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Search products by term.
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return $this->model->search($term)
            ->active()
            ->limit($limit)
            ->get();
    }

    /**
     * Get products by category.
     */
    public function getByCategory(int $categoryId, bool $activeOnly = true): Collection
    {
        $query = $this->model->byCategory($categoryId);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get products by brand.
     */
    public function getByBrand(string $brand, bool $activeOnly = true): Collection
    {
        $query = $this->model->where('brand', $brand);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get products by supplier.
     */
    public function getBySupplier(string $supplier, bool $activeOnly = true): Collection
    {
        $query = $this->model->where('supplier', $supplier);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get products by price range.
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, bool $activeOnly = true): Collection
    {
        $query = $this->model->byPriceRange($minPrice, $maxPrice);

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('price')->get();
    }

    /**
     * Get products with barcodes.
     */
    public function getWithBarcodes(bool $activeOnly = true): Collection
    {
        $query = $this->model->whereNotNull('barcode');

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get products without barcodes.
     */
    public function getWithoutBarcodes(bool $activeOnly = true): Collection
    {
        $query = $this->model->whereNull('barcode');

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get low stock products (placeholder for when stock system is implemented).
     */
    public function getLowStockProducts(): Collection
    {
        // This will be implemented when we create the Stock model
        // For now, return empty collection
        return collect();
    }

    /**
     * Get products for dropdown/select options.
     */
    public function getForDropdown(?int $categoryId = null): Collection
    {
        $query = $this->model->active()->orderBy('name');

        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        return $query->get(['id', 'name', 'product_code', 'barcode', 'price']);
    }

    /**
     * Get products with category information.
     */
    public function getWithCategory(): Collection
    {
        return $this->model->with('category')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Count products by status.
     */
    public function countByStatus(bool $active = true): int
    {
        return $this->model->where('is_active', $active)->count();
    }

    /**
     * Count products by category.
     */
    public function countByCategory(): Collection
    {
        return $this->model->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(*) as product_count')
            ->where('products.is_active', true)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('category_name')
            ->get();
    }

    /**
     * Get products with profit margins.
     */
    public function getWithProfitMargins(): Collection
    {
        return $this->model->whereNotNull('cost_price')
            ->where('cost_price', '>', 0)
            ->active()
            ->with('category')
            ->orderByRaw('(price - cost_price) / cost_price DESC')
            ->get();
    }

    /**
     * Bulk update products.
     */
    public function bulkUpdate(array $updates): int
    {
        $updated = 0;

        try {
            \DB::transaction(function () use ($updates, &$updated) {
                foreach ($updates as $id => $data) {
                    $product = $this->model->find($id);
                    if ($product) {
                        $product->update($data);
                        $updated++;
                    }
                }
            });
        } catch (\Exception $e) {
            // Log error if needed
        }

        return $updated;
    }

    /**
     * Generate unique product code.
     */
    public function generateUniqueProductCode(): string
    {
        do {
            $prefix = 'PRD';
            $timestamp = now()->format('Ymd');
            $random = mt_rand(1000, 9999);
            $productCode = "{$prefix}{$timestamp}{$random}";
        } while ($this->productCodeExists($productCode));

        return $productCode;
    }

    /**
     * Get products by multiple criteria.
     */
    public function getByMultipleCriteria(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query->active()->orderBy('name')->get();
    }

    /**
     * Get products with their current stock (placeholder).
     */
    public function getWithStock(): Collection
    {
        // This will be implemented when we create the Stock model
        return $this->model->active()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Update product status (active/inactive).
     */
    public function updateStatus(int $id, bool $isActive): bool
    {
        return $this->model->where('id', $id)
            ->update(['is_active' => $isActive]) > 0;
    }

    /**
     * Get products that need reordering (placeholder).
     */
    public function getProductsNeedingReorder(): Collection
    {
        // This will be implemented when we create the Stock model
        return collect();
    }

    /**
     * Get the model instance.
     */
    public function getModel(): Product
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
     * Get products with their relationships loaded.
     */
    public function getWithRelations(array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get products by multiple IDs.
     */
    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)
            ->active()
            ->orderBy('name')
            ->get();
    }
}
