<?php

namespace App\Repositories\Interfaces;

use App\Models\SalesOrder;
use Illuminate\Pagination\LengthAwarePaginator;

interface SalesOrderRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findByNumber(string $number): ?SalesOrder;

    public function findByCustomer(int $customerId): \Illuminate\Database\Eloquent\Collection;

    public function getPendingOrders(): \Illuminate\Database\Eloquent\Collection;

    public function getCompletedOrders(): \Illuminate\Database\Eloquent\Collection;
}
