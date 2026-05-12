<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepository;
use App\DTOs\Product\ProductData;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductRepository extends EloquentModelRepository implements ProductRepository
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('category')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('sku', 'like', "%{$s}%")
                      ->orWhere('barcode', 'like', "%{$s}%")
                      ->orWhere('brand', 'like', "%{$s}%")
                      ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
                });
            })
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(!empty($filters['category_id']), fn ($q) => $q->where('product_category_id', $filters['category_id']))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
