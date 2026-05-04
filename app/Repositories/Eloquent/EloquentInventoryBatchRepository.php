<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryBatch;
use App\Repositories\Interfaces\InventoryBatchRepository;

class EloquentInventoryBatchRepository extends EloquentModelRepository implements InventoryBatchRepository
{
    public function __construct(InventoryBatch $inventoryBatch)
    {
        parent::__construct($inventoryBatch);
    }

    public function findByBatchNumber(string $batchNumber): ?InventoryBatch
    {
        return $this->findBy('batch_number', $batchNumber);
    }

    public function findByProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('product_id', $productId);
    }

    public function getExpiringBatches(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->where('expiry_date', '<=', now()->addDays($days))->get();
    }
}
