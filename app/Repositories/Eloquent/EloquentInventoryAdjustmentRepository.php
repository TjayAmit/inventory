<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryAdjustment;
use App\Repositories\Interfaces\InventoryAdjustmentRepository;

class EloquentInventoryAdjustmentRepository extends EloquentModelRepository implements InventoryAdjustmentRepository
{
    public function __construct(InventoryAdjustment $inventoryAdjustment)
    {
        parent::__construct($inventoryAdjustment);
    }

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('product_id', $productId);
    }

    public function findByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('branch_id', $branchId);
    }

    public function findByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('adjustment_type', $type);
    }
}
