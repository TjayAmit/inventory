<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepository;
use App\DTOs\Product\ProductData;

class EloquentProductRepository extends EloquentModelRepository implements ProductRepository
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->findBy('sku', $sku);
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return $this->findBy('barcode', $barcode);
    }

    public function createFromData(ProductData $data): Product
    {
        return $this->create($data->toArray());
    }

    public function updateFromData(int $id, ProductData $data): Product
    {
        return $this->update($id, $data->toArray());
    }

    public function getActiveProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->active()->get();
    }

    public function getTrackableProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->trackable()->get();
    }

    public function getProductsNeedingReorder(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereRaw('(SELECT SUM(quantity_on_hand) FROM inventory WHERE inventory.product_id = products.id) <= reorder_level')->get();
    }
}
