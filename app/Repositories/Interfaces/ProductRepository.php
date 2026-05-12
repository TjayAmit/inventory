<?php

namespace App\Repositories\Interfaces;

use App\DTOs\Product\ProductData;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findBySku(string $sku): ?Product;

    public function findByBarcode(string $barcode): ?Product;

    public function createFromData(ProductData $data): Product;

    public function updateFromData(int $id, ProductData $data): Product;

    public function getActiveProducts(): \Illuminate\Database\Eloquent\Collection;

    public function getTrackableProducts(): \Illuminate\Database\Eloquent\Collection;

    public function getProductsNeedingReorder(): \Illuminate\Database\Eloquent\Collection;
}
