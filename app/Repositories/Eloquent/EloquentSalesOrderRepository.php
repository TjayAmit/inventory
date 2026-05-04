<?php

namespace App\Repositories\Eloquent;

use App\Models\SalesOrder;
use App\Repositories\Interfaces\SalesOrderRepository;

class EloquentSalesOrderRepository extends EloquentModelRepository implements SalesOrderRepository
{
    public function __construct(SalesOrder $salesOrder)
    {
        parent::__construct($salesOrder);
    }

    public function findByNumber(string $number): ?SalesOrder
    {
        return $this->findBy('order_number', $number);
    }

    public function findByCustomer(int $customerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('customer_id', $customerId);
    }

    public function getPendingOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('status', 'pending');
    }

    public function getCompletedOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('status', 'completed');
    }
}
