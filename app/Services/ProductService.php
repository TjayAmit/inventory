<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepository;
use App\DTOs\Product\ProductData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $productRepository)
    {
        $this->repository = $productRepository;
    }

    public function create(Request $request): Product
    {
        $model = null;
        $dto = null;

        $this->executeInTransaction(function () use ($request, &$model, &$dto) {
            $dto = ProductData::fromRequest($request);
            $model = $this->repository->createFromData($dto);
        });

        $this->logActivity('created', $model, $dto->toArray());

        return $model;
    }

    public function update(Request $request, Product $product): Product
    {
        $old = $product->getOriginal();
        $dto = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $product, &$dto, &$updated) {
            $dto = ProductData::fromRequest($request);
            $updated = $this->repository->updateFromData($product->id, $dto);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $dto->toArray()]);

        return $updated;
    }

    public function delete(Product $product): bool
    {
        $data = $product->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($product, &$result) {
            $result = $this->repository->delete($product->id);
        });

        $this->logActivity('deleted', $product, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return ProductData::class;
    }
}
