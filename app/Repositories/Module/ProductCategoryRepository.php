<?php

namespace App\Repositories\Module;

use App\Models\ProductCategory;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class ProductCategoryRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(ProductCategory $productCategory)
    {
        parent::__construct($productCategory);
    }

    public function getModuleName(): string
    {
        return 'product_category';
    }

    protected function getModelClass(): string
    {
        return ProductCategory::class;
    }
}
