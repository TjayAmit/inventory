<?php

namespace App\Services\Module;

use App\DTOs\Product\ProductData;
use App\Models\Product;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return ProductData::class;
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function getModuleName(): string
    {
        return 'product';
    }

    // Product-specific business logic methods
    public function getActiveProducts()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getSellableProducts()
    {
        return $this->repository->where('is_sellable', true)->get();
    }

    public function getTrackableProducts()
    {
        return $this->repository->where('is_trackable', true)->get();
    }

    public function getTaxableProducts()
    {
        return $this->repository->where('is_taxable', true)->get();
    }

    public function getProductsByCategory(int $categoryId)
    {
        return $this->repository->where('category_id', $categoryId)->get();
    }

    public function getProductsBySupplier(?int $supplierId)
    {
        if (!$supplierId) {
            return $this->repository->all();
        }
        
        return $this->repository->where('supplier_id', $supplierId)->get();
    }

    public function getLowStockProducts()
    {
        return $this->repository->whereHas('inventory', function ($query) {
            $query->whereColumn('quantity_on_hand', '<=', 'reorder_point');
        })->get();
    }

    public function getOutOfStockProducts()
    {
        return $this->repository->whereHas('inventory', function ($query) {
            $query->where('quantity_on_hand', '<=', 0);
        })->get();
    }

    public function searchProducts(string $searchTerm)
    {
        return $this->repository->where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
            ->orWhere('barcode', 'LIKE', "%{$searchTerm}%")
            ->orWhere('brand', 'LIKE', "%{$searchTerm}%")
            ->orWhere('model', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    public function getProductsByPriceRange(float $minPrice, float $maxPrice)
    {
        return $this->repository->whereBetween('selling_price', [$minPrice, $maxPrice])->get();
    }

    public function getProductsByBrand(string $brand)
    {
        return $this->repository->where('brand', $brand)->get();
    }

    public function getProductsWithInventory()
    {
        return $this->repository->with(['inventory', 'category'])->get();
    }

    public function getProductsWithInventoryCount()
    {
        return $this->repository->with(['inventory'])
            ->selectRaw('products.*, COUNT(inventory.id) as inventory_count')
            ->groupBy('products.id')
            ->get();
    }

    public function getProductsCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveProductsCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getSellableProductsCount(): int
    {
        return $this->repository->where('is_sellable', true)->count();
    }

    public function getTrackableProductsCount(): int
    {
        return $this->repository->where('is_trackable', true)->count();
    }

    public function getTaxableProductsCount(): int
    {
        return $this->repository->where('is_taxable', true)->count();
    }

    public function isProductSkuUnique(string $sku, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('sku', $sku);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isProductBarcodeUnique(string $barcode, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('barcode', $barcode);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getProductsWithImages()
    {
        return $this->repository->whereNotNull('image_urls')->get();
    }

    public function getProductsWithoutImages()
    {
        return $this->repository->whereNull('image_urls')->get();
    }

    public function getProductsWithAttributes()
    {
        return $this->repository->whereNotNull('attributes')->get();
    }

    public function getProductsWithoutAttributes()
    {
        return $this->repository->whereNull('attributes')->get();
    }

    public function getProductsNeedingReorder()
    {
        return $this->repository->whereHas('inventory', function ($query) {
            $query->whereColumn('quantity_on_hand', '<=', 'reorder_point');
        })->where('is_active', true)->get();
    }
}
