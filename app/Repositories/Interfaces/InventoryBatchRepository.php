<?php

namespace App\Repositories\Interfaces;

use App\Models\InventoryBatch;

interface InventoryBatchRepository extends ModelRepository
{
    public function findByBatchNumber(string $batchNumber): ?InventoryBatch;
    
    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection;
    
    public function getExpiringBatches(int $days = 30): \Illuminate\Database\Eloquent\Collection;
}
