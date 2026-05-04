<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseOrder;
use App\Repositories\Interfaces\PurchaseOrderRepository;

class EloquentPurchaseOrderRepository extends EloquentModelRepository implements PurchaseOrderRepository
{
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        parent::__construct($purchaseOrder);
    }

    public function findByNumber(string $number): ?PurchaseOrder
    {
        return $this->findBy('order_number', $number);
    }

    public function findBySupplier(int $supplierId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('supplier_id', $supplierId);
    }

    public function getPendingOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('status', 'pending');
    }

    public function getApprovedOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('status', 'approved');
    }
}
