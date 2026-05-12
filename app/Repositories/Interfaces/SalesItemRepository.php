<?php

namespace App\Repositories\Interfaces;

use App\Models\SalesItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface SalesItemRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findBySalesOrder(int $salesOrderId): \Illuminate\Database\Eloquent\Collection;

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
}
