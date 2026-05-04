<?php

namespace App\Repositories\Interfaces;

use App\Models\SalesItem;

interface SalesItemRepository extends ModelRepository
{
    public function findBySalesOrder(int $salesOrderId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
}
