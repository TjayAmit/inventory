<?php

namespace App\Repositories\Interfaces;

use App\Models\Inventory;

interface InventoryRepository extends ModelRepository
{
    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection;
    
    public function findByProductAndBranch(int $productId, int $branchId): ?Inventory;
    
    public function updateQuantity(int $id, int $quantity): Inventory;
    
    public function getLowStockItems(int $threshold = 10): \Illuminate\Database\Eloquent\Collection;
}
