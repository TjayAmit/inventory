<?php

namespace App\Repositories\Module;

use App\Models\Product;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class ProductRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function getModuleName(): string
    {
        return 'product';
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }
}
