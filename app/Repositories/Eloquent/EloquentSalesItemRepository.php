<?php

namespace App\Repositories\Eloquent;

use App\Models\SalesItem;
use App\Repositories\Interfaces\SalesItemRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentSalesItemRepository extends EloquentModelRepository implements SalesItemRepository
{
    public function __construct(SalesItem $salesItem)
    {
        parent::__construct($salesItem);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->with(['salesOrder', 'product'])
            ->when(!empty($filters['sales_order_id']), fn ($q) => $q->where('sales_order_id', $filters['sales_order_id']))
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
