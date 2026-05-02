<?php

namespace App\Services\Module;

use App\DTOs\ProductCategory\ProductCategoryData;
use App\Models\ProductCategory;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return ProductCategoryData::class;
    }

    protected function getModelClass(): string
    {
        return ProductCategory::class;
    }

    public function getModuleName(): string
    {
        return 'product_category';
    }

    // Product category-specific business logic methods
    public function getRootCategories()
    {
        return $this->repository->whereNull('parent_id')->get();
    }

    public function getChildCategories(int $parentId)
    {
        return $this->repository->where('parent_id', $parentId)->get();
    }

    public function getActiveCategories()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getCategoriesWithProducts()
    {
        return $this->repository->with(['products'])->get();
    }

    public function getCategoriesWithProductsCount()
    {
        return $this->repository->with(['products'])
            ->selectRaw('product_categories.*, COUNT(products.id) as products_count')
            ->groupBy('product_categories.id')
            ->get();
    }

    public function getCategoriesOrdered()
    {
        return $this->repository->orderBy('sort_order')->orderBy('name')->get();
    }

    public function searchCategories(string $searchTerm)
    {
        return $this->repository->where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    public function getCategoriesWithChildren()
    {
        return $this->repository->with(['children'])->get();
    }

    public function getCategoryWithParent()
    {
        return $this->repository->with(['parent'])->get();
    }

    public function getCategoriesCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveCategoriesCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getRootCategoriesCount(): int
    {
        return $this->repository->whereNull('parent_id')->count();
    }

    public function getChildCategoriesCount(int $parentId): int
    {
        return $this->repository->where('parent_id', $parentId)->count();
    }

    public function isCategoryNameUnique(string $name, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('name', $name);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isCategorySlugUnique(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('slug', $slug);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getFullCategoryTree()
    {
        $categories = $this->repository->whereNull('parent_id')->with(['children' => function ($query) {
            $query->with(['children' => function ($subQuery) {
                $subQuery->with(['children' => function ($subSubQuery) {
                    $subSubQuery->with(['children']);
                }]);
            }]);
        }])->get();

        return $categories;
    }

    public function getCategoryPath(int $categoryId): ?string
    {
        $category = $this->repository->find($categoryId);
        
        if (!$category) {
            return null;
        }

        $path = [];
        $current = $category;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode(' > ', $path);
    }

    public function getCategoriesByParent(int $parentId)
    {
        return $this->repository->where('parent_id', $parentId)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getCategoriesWithImage()
    {
        return $this->repository->whereNotNull('image_url')->get();
    }

    public function getCategoriesWithoutImage()
    {
        return $this->repository->whereNull('image_url')->get();
    }

    public function getCategoriesWithDescription()
    {
        return $this->repository->whereNotNull('description')->get();
    }

    public function getCategoriesWithoutDescription()
    {
        return $this->repository->whereNull('description')->get();
    }
}
