<?php

namespace App\Repositories\Interfaces;

use App\Models\SalesOrder;

interface SalesOrderRepository extends ModelRepository
{
    public function findByNumber(string $number): ?SalesOrder;
    
    public function findByCustomer(int $customerId): \Illuminate\Database\Eloquent\Collection;
    
    public function getPendingOrders(): \Illuminate\Database\Eloquent\Collection;
    
    public function getCompletedOrders(): \Illuminate\Database\Eloquent\Collection;
}
