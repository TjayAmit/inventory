<?php

namespace App\Repositories\Interfaces;

use App\Models\PurchaseOrderItem;

interface PurchaseOrderItemRepository extends ModelRepository
{
    public function findByPurchaseOrder(int $purchaseOrderId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
}
