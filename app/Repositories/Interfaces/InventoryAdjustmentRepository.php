<?php

namespace App\Repositories\Interfaces;

use App\Models\InventoryAdjustment;

interface InventoryAdjustmentRepository extends ModelRepository
{
    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByType(string $type): \Illuminate\Database\Eloquent\Collection;
}
