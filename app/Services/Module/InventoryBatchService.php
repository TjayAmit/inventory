<?php

namespace App\Services\Module;

use App\DTOs\InventoryBatch\InventoryBatchData;
use App\Models\InventoryBatch;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryBatchService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return InventoryBatchData::class;
    }

    protected function getModelClass(): string
    {
        return InventoryBatch::class;
    }

    public function getModuleName(): string
    {
        return 'inventory_batch';
    }

    // Inventory batch-specific business logic methods
    public function getActiveBatches()
    {
        return $this->repository->where('quantity_remaining', '>', 0)->get();
    }

    public function getExpiredBatches()
    {
        return $this->repository->where('expiry_date', '<', now())->get();
    }

    public function getBatchesExpiringSoon(int $days = 30)
    {
        return $this->repository->where('expiry_date', '<=', now()->addDays($days))->get();
    }

    public function getBatchesByProduct(int $productId)
    {
        return $this->repository->where('product_id', $productId)->get();
    }

    public function getBatchesByInventory(int $inventoryId)
    {
        return $this->repository->where('inventory_id', $inventoryId)->get();
    }

    public function getBatchesBySupplierReference(string $supplierBatchRef)
    {
        return $this->repository->where('supplier_batch_ref', $supplierBatchRef)->get();
    }

    public function getBatchesByDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('received_date', [$startDate, $endDate])->get();
    }

    public function getBatchesWithProduct()
    {
        return $this->repository->with(['product'])->get();
    }

    public function getBatchesWithInventory()
    {
        return $this->repository->with(['inventory'])->get();
    }

    public function getBatchesWithProductAndInventory()
    {
        return $this->repository->with(['product', 'inventory'])->get();
    }

    public function getBatchesCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveBatchesCount(): int
    {
        return $this->repository->where('quantity_remaining', '>', 0)->count();
    }

    public function getExpiredBatchesCount(): int
    {
        return $this->repository->where('expiry_date', '<', now())->count();
    }

    public function getBatchesExpiringSoonCount(int $days = 30): int
    {
        return $this->repository->where('expiry_date', '<=', now()->addDays($days))->count();
    }

    public function getTotalBatchesValue(): float
    {
        return $this->repository->sum('total_cost');
    }

    public function getTotalBatchesQuantity(): int
    {
        return $this->repository->sum('quantity');
    }

    public function getTotalRemainingQuantity(): int
    {
        return $this->repository->sum('quantity_remaining');
    }

    public function getTotalSoldQuantity(): int
    {
        return $this->repository->sum('quantity_sold');
    }

    public function getBatchesByUnitCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('unit_cost', [$minCost, $maxCost])->get();
    }

    public function isBatchNumberUnique(string $batchNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('batch_number', $batchNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function consumeFromBatch(int $batchId, int $quantity): bool
    {
        $batch = $this->repository->find($batchId);
        
        if (!$batch || $batch->quantity_remaining < $quantity) {
            return false;
        }

        $batch->consumeQuantity($quantity);
        return $batch->save();
    }

    public function getBatchesFifoOrder()
    {
        return $this->repository->orderBy('received_date')->orderBy('id')->get();
    }

    public function getBatchesByExpiryOrder()
    {
        return $this->repository->orderBy('expiry_date')->get();
    }

    public function getBatchAgingReport()
    {
        return $this->repository->with(['product'])
            ->selectRaw('inventory_batches.*, DATEDIFF(NOW(), expiry_date) as days_to_expiry')
            ->orderBy('days_to_expiry')
            ->get();
    }
}
