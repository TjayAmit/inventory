<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseOrderItem;
use App\Repositories\Interfaces\PurchaseOrderItemRepository;

class EloquentPurchaseOrderItemRepository extends EloquentModelRepository implements PurchaseOrderItemRepository
{
    public function __construct(PurchaseOrderItem $purchaseOrderItem)
    {
        parent::__construct($purchaseOrderItem);
    }

    public function findByPurchaseOrder(int $purchaseOrderId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('purchase_order_id', $purchaseOrderId);
    }

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('product_id', $productId);
    }
}
