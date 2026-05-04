<?php

namespace App\Repositories\Eloquent;

use App\Models\SalesItem;
use App\Repositories\Interfaces\SalesItemRepository;

class EloquentSalesItemRepository extends EloquentModelRepository implements SalesItemRepository
{
    public function __construct(SalesItem $salesItem)
    {
        parent::__construct($salesItem);
    }

    public function findBySalesOrder(int $salesOrderId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('sales_order_id', $salesOrderId);
    }

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('product_id', $productId);
    }
}
