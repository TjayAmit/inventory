<?php

namespace App\Repositories\Interfaces;

use App\Models\PurchaseOrder;

interface PurchaseOrderRepository extends ModelRepository
{
    public function findByNumber(string $number): ?PurchaseOrder;
    
    public function findBySupplier(int $supplierId): \Illuminate\Database\Eloquent\Collection;
    
    public function getPendingOrders(): \Illuminate\Database\Eloquent\Collection;
    
    public function getApprovedOrders(): \Illuminate\Database\Eloquent\Collection;
}
