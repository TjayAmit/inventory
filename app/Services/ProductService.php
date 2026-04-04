<?php

namespace App\Services;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\ProductFiltersDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get paginated products with filters.
     */
    public function getPaginatedProducts(ProductFiltersDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->productRepository->paginateWithFilters($filters, $perPage);
        
        // Transform items to DTOs
        $paginator->getCollection()->transform(function ($product) {
            return ProductResponseDTO::fromModel($product, true, false);
        });

        return $paginator;
    }

    /**
     * Get all active products.
     */
    public function getActiveProducts(): Collection
    {
        $products = $this->productRepository->getActive();
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, true, false);
        });
    }

    /**
     * Find product by ID.
     */
    public function getProductById(int $id): ?ProductResponseDTO
    {
        $product = $this->productRepository->findById($id, ['category']);
        
        if (!$product) {
            return null;
        }
        
        return ProductResponseDTO::fromModel($product, true, false);
    }

    /**
     * Find product by barcode.
     */
    public function getProductByBarcode(string $barcode): ?ProductResponseDTO
    {
        $product = $this->productRepository->findByBarcode($barcode);
        
        if (!$product) {
            return null;
        }
        
        return ProductResponseDTO::fromModel($product, false, false);
    }

    /**
     * Find product by product code.
     */
    public function getProductByProductCode(string $productCode): ?ProductResponseDTO
    {
        $product = $this->productRepository->findByProductCode($productCode);
        
        if (!$product) {
            return null;
        }
        
        return ProductResponseDTO::fromModel($product, false, false);
    }

    /**
     * Create a new product.
     */
    public function createProduct(CreateProductDTO $dto): ProductResponseDTO
    {
        // Additional business logic validations
        $this->validateProductCreation($dto);

        try {
            DB::beginTransaction();

            // Generate product code if not provided
            if (empty($dto->getProductCode())) {
                $dto = new CreateProductDTO(
                    name: $dto->getName(),
                    productCode: $this->productRepository->generateUniqueProductCode(),
                    barcode: $dto->getBarcode(),
                    description: $dto->getDescription(),
                    price: $dto->getPrice(),
                    costPrice: $dto->getCostPrice(),
                    categoryId: $dto->getCategoryId(),
                    isActive: $dto->getIsActive(),
                    isTaxable: $dto->getIsTaxable(),
                    unit: $dto->getUnit(),
                    weight: $dto->getWeight(),
                    volume: $dto->getVolume(),
                    brand: $dto->getBrand(),
                    manufacturer: $dto->getManufacturer(),
                    supplier: $dto->getSupplier(),
                    reorderPoint: $dto->getReorderPoint(),
                    maxStock: $dto->getMaxStock(),
                    notes: $dto->getNotes()
                );
            }

            $product = $this->productRepository->create($dto);

            DB::commit();

            return ProductResponseDTO::fromModel($product, true, false);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw new \RuntimeException('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(int $id, UpdateProductDTO $dto): ProductResponseDTO
    {
        $product = $this->productRepository->findById($id);
        
        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        // Additional business logic validations
        $this->validateProductUpdate($product, $dto);

        try {
            DB::beginTransaction();

            $updatedProduct = $this->productRepository->update($id, $dto);

            DB::commit();

            return ProductResponseDTO::fromModel($updatedProduct, true, false);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw new \RuntimeException('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);
        
        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        // Business rule: Cannot delete product with sales records
        if ($product->saleItems()->exists()) {
            throw new \InvalidArgumentException('Cannot delete product that has sales records. Consider deactivating it instead.');
        }

        try {
            DB::beginTransaction();

            $result = $this->productRepository->delete($id);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Search products.
     */
    public function searchProducts(string $term, int $limit = 10): Collection
    {
        $products = $this->productRepository->search($term, $limit);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products by category.
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        $products = $this->productRepository->getByCategory($categoryId);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products by brand.
     */
    public function getProductsByBrand(string $brand): Collection
    {
        $products = $this->productRepository->getByBrand($brand);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products by supplier.
     */
    public function getProductsBySupplier(string $supplier): Collection
    {
        $products = $this->productRepository->getBySupplier($supplier);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products by price range.
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        $products = $this->productRepository->getByPriceRange($minPrice, $maxPrice);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products with barcodes.
     */
    public function getProductsWithBarcodes(): Collection
    {
        $products = $this->productRepository->getWithBarcodes();
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products without barcodes.
     */
    public function getProductsWithoutBarcodes(): Collection
    {
        $products = $this->productRepository->getWithoutBarcodes();
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Get products for dropdown/select.
     */
    public function getProductsForDropdown(?int $categoryId = null): Collection
    {
        return $this->productRepository->getForDropdown($categoryId);
    }

    /**
     * Get products with profit margins.
     */
    public function getProductsWithProfitMargins(): Collection
    {
        $products = $this->productRepository->getWithProfitMargins();
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, true, false);
        });
    }

    /**
     * Toggle product active status.
     */
    public function toggleProductStatus(int $id): ProductResponseDTO
    {
        $product = $this->productRepository->findById($id);
        
        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        $newStatus = !$product->is_active;

        // Business rule: Cannot deactivate product with active stock (placeholder)
        // This will be implemented when we create the Stock model

        $result = $this->productRepository->updateStatus($id, $newStatus);
        
        if (!$result) {
            throw new \RuntimeException('Failed to update product status.');
        }

        return $this->getProductById($id);
    }

    /**
     * Bulk update products.
     */
    public function bulkUpdateProducts(array $updates): int
    {
        try {
            DB::beginTransaction();

            $updated = $this->productRepository->bulkUpdate($updates);

            DB::commit();

            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Failed to bulk update products: ' . $e->getMessage());
        }
    }

    /**
     * Generate barcode for product.
     */
    public function generateBarcodeForProduct(int $productId): ProductResponseDTO
    {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        if ($product->barcode) {
            throw new \InvalidArgumentException('Product already has a barcode.');
        }

        $barcode = Product::generateDummyBarcode();

        $dto = new UpdateProductDTO(
            name: $product->name,
            productCode: $product->product_code,
            price: $product->price,
            barcode: $barcode,
            productId: $productId
        );

        return $this->updateProduct($productId, $dto);
    }

    /**
     * Get product statistics.
     */
    public function getProductStatistics(): array
    {
        $totalProducts = $this->productRepository->getModel()->count();
        $activeProducts = $this->productRepository->countByStatus(true);
        $inactiveProducts = $this->productRepository->countByStatus(false);
        $productsWithBarcodes = $this->productRepository->getWithBarcodes()->count();
        $productsWithoutBarcodes = $this->productRepository->getWithoutBarcodes()->count();

        return [
            'total' => $totalProducts,
            'active' => $activeProducts,
            'inactive' => $inactiveProducts,
            'with_barcodes' => $productsWithBarcodes,
            'without_barcodes' => $productsWithoutBarcodes,
            'active_percentage' => $totalProducts > 0 ? round(($activeProducts / $totalProducts) * 100, 2) : 0,
            'barcode_coverage' => $totalProducts > 0 ? round(($productsWithBarcodes / $totalProducts) * 100, 2) : 0,
        ];
    }

    /**
     * Get products by multiple criteria.
     */
    public function getProductsByCriteria(array $criteria): Collection
    {
        $products = $this->productRepository->getByMultipleCriteria($criteria);
        
        return $products->map(function ($product) {
            return ProductResponseDTO::fromModel($product, false, false);
        });
    }

    /**
     * Validate product creation business rules.
     */
    private function validateProductCreation(CreateProductDTO $dto): void
    {
        // Check if product code already exists
        if ($this->productRepository->productCodeExists($dto->getProductCode())) {
            throw ValidationException::withMessages([
                'productCode' => 'A product with this code already exists.'
            ]);
        }

        // Check if barcode already exists
        if ($dto->getBarcode() && $this->productRepository->barcodeExists($dto->getBarcode())) {
            throw ValidationException::withMessages([
                'barcode' => 'A product with this barcode already exists.'
            ]);
        }

        // Validate category if specified
        if ($dto->getCategoryId()) {
            $category = \App\Models\Category::find($dto->getCategoryId());
            if (!$category) {
                throw ValidationException::withMessages([
                    'categoryId' => 'The selected category does not exist.'
                ]);
            }

            if (!$category->is_active) {
                throw ValidationException::withMessages([
                    'categoryId' => 'Cannot assign to an inactive category.'
                ]);
            }
        }
    }

    /**
     * Validate product update business rules.
     */
    private function validateProductUpdate(Product $product, UpdateProductDTO $dto): void
    {
        // Check if product code already exists (excluding current product)
        if ($this->productRepository->productCodeExists($dto->getProductCode(), $product->id)) {
            throw ValidationException::withMessages([
                'productCode' => 'A product with this code already exists.'
            ]);
        }

        // Check if barcode already exists (excluding current product)
        if ($dto->getBarcode() && $this->productRepository->barcodeExists($dto->getBarcode(), $product->id)) {
            throw ValidationException::withMessages([
                'barcode' => 'A product with this barcode already exists.'
            ]);
        }

        // Validate category if being changed
        if ($dto->getCategoryId() !== null && $dto->getCategoryId() != $product->category_id) {
            $category = \App\Models\Category::find($dto->getCategoryId());
            if (!$category) {
                throw ValidationException::withMessages([
                    'categoryId' => 'The selected category does not exist.'
                ]);
            }

            if (!$category->is_active) {
                throw ValidationException::withMessages([
                    'categoryId' => 'Cannot assign to an inactive category.'
                ]);
            }
        }

        // Validate deactivation if product has sales
        if ($dto->getIsActive() === false && $product->saleItems()->exists()) {
            throw ValidationException::withMessages([
                'isActive' => 'Cannot deactivate product that has sales records.'
            ]);
        }
    }

    /**
     * Get the repository instance.
     */
    public function getRepository(): ProductRepositoryInterface
    {
        return $this->productRepository;
    }

    /**
     * Import products from array (for bulk import functionality).
     */
    public function importProducts(array $productsData): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            DB::beginTransaction();

            foreach ($productsData as $index => $productData) {
                try {
                    // Generate product code if not provided
                    if (empty($productData['product_code'])) {
                        $productData['product_code'] = $this->productRepository->generateUniqueProductCode();
                    }

                    $dto = new CreateProductDTO(
                        name: $productData['name'],
                        productCode: $productData['product_code'],
                        barcode: $productData['barcode'] ?? null,
                        description: $productData['description'] ?? null,
                        price: (float) $productData['price'],
                        costPrice: isset($productData['cost_price']) ? (float) $productData['cost_price'] : null,
                        categoryId: isset($productData['category_id']) ? (int) $productData['category_id'] : null,
                        isActive: $productData['is_active'] ?? true,
                        isTaxable: $productData['is_taxable'] ?? true,
                        unit: $productData['unit'] ?? 'pcs',
                        weight: isset($productData['weight']) ? (float) $productData['weight'] : null,
                        volume: isset($productData['volume']) ? (float) $productData['volume'] : null,
                        brand: $productData['brand'] ?? null,
                        manufacturer: $productData['manufacturer'] ?? null,
                        supplier: $productData['supplier'] ?? null,
                        reorderPoint: $productData['reorder_point'] ?? 10,
                        maxStock: $productData['max_stock'] ?? 1000,
                        notes: $productData['notes'] ?? null
                    );

                    $this->productRepository->create($dto);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Failed to import products: ' . $e->getMessage());
        }

        return $results;
    }
}
