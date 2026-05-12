<?php

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Repositories\Interfaces\InventoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentInventoryRepository extends EloquentModelRepository implements InventoryRepository
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->with(['product', 'branch'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%"));
            })
            ->when(!empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('product_id', $productId);
    }

    public function findByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('branch_id', $branchId);
    }

    public function findByProductAndBranch(int $productId, int $branchId): ?Inventory
    {
        return $this->query->where('product_id', $productId)->where('branch_id', $branchId)->first();
    }

    public function updateQuantity(int $id, int $quantity): Inventory
    {
        $inventory = $this->findOrFail($id);
        $inventory->update(['quantity_on_hand' => $quantity]);
        return $inventory;
    }

    public function getLowStockItems(int $threshold = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->where('quantity_on_hand', '<=', $threshold)->get();
    }
}
