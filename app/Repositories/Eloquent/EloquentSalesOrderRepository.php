<?php

namespace App\Repositories\Eloquent;

use App\Models\SalesOrder;
use App\Repositories\Interfaces\SalesOrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentSalesOrderRepository extends EloquentModelRepository implements SalesOrderRepository
{
    public function __construct(SalesOrder $salesOrder)
    {
        parent::__construct($salesOrder);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->with(['branch', 'cashier'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($q) use ($s) {
                    $q->where('order_number', 'like', "%{$s}%")
                      ->orWhere('status', 'like', "%{$s}%")
                      ->orWhere('payment_status', 'like', "%{$s}%")
                      ->orWhereHas('branch', fn ($bq) => $bq->where('name', 'like', "%{$s}%"))
                      ->orWhereHas('cashier', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
                });
            })
            ->when(!empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['payment_status']), fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->when(!empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
