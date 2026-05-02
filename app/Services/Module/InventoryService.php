<?php

namespace App\Services\Module;

use App\DTOs\Inventory\InventoryData;
use App\Models\Inventory;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return InventoryData::class;
    }

    protected function getModelClass(): string
    {
        return Inventory::class;
    }

    public function getModuleName(): string
    {
        return 'inventory';
    }

    // Inventory-specific business logic methods
    public function getInventoryByProduct(int $productId)
    {
        return $this->repository->where('product_id', $productId)->get();
    }

    public function getInventoryByBranch(int $branchId)
    {
        return $this->repository->where('branch_id', $branchId)->get();
    }

    public function getInventoryByProductAndBranch(int $productId, int $branchId)
    {
        return $this->repository->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();
    }

    public function getActiveInventory()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getLowStockInventory()
    {
        return $this->repository->whereHas('product', function ($query) {
            $query->where('quantity_on_hand', '<=', 'reorder_point');
        })->get();
    }

    public function getOutOfStockInventory()
    {
        return $this->repository->whereHas('product', function ($query) {
            $query->where('quantity_on_hand', '<=', 0);
        })->get();
    }

    public function getInventoryWithBatches()
    {
        return $this->repository->with(['batches'])->get();
    }

    public function getInventoryWithProducts()
    {
        return $this->repository->with(['product'])->get();
    }

    public function getInventoryWithBranch()
    {
        return $this->repository->with(['branch'])->get();
    }

    public function getInventoryWithProductAndBranch()
    {
        return $this->repository->with(['product', 'branch'])->get();
    }

    public function getInventoryCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveInventoryCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getLowStockCount(): int
    {
        return $this->repository->whereHas('product', function ($query) {
            $query->where('quantity_on_hand', '<=', 'reorder_point');
        })->count();
    }

    public function getOutOfStockCount(): int
    {
        return $this->repository->whereHas('product', function ($query) {
            $query->where('quantity_on_hand', '<=', 0);
        })->count();
    }

    public function getTotalStockValue(): float
    {
        return $this->repository->sum('total_cost');
    }

    public function getTotalStockQuantity(): int
    {
        return $this->repository->sum('quantity_on_hand');
    }

    public function getAvailableStockQuantity(): int
    {
        return $this->repository->sum('quantity_available');
    }

    public function getReservedStockQuantity(): int
    {
        return $this->repository->sum('quantity_reserved');
    }

    public function getInventoryByReorderPoint(float $minReorderPoint, float $maxReorderPoint)
    {
        return $this->repository->whereBetween('reorder_point', [$minReorderPoint, $maxReorderPoint])->get();
    }

    public function getInventoryByStockRange(int $minStock, int $maxStock)
    {
        return $this->repository->whereBetween('quantity_on_hand', [$minStock, $maxStock])->get();
    }

    public function getInventoryNeedingReorder()
    {
        return $this->repository->whereHas('product', function ($query) {
            $query->whereColumn('quantity_on_hand', '<=', 'reorder_point');
        })->where('is_active', true)->get();
    }

    public function updateStockLevels(array $inventoryIds): void
    {
        $this->repository->whereIn('id', $inventoryIds)
            ->get()
            ->each(function ($inventory) {
                $inventory->updateAvailableQuantity();
            });
    }

    public function reserveStock(int $inventoryId, int $quantity): bool
    {
        $inventory = $this->repository->find($inventoryId);
        
        if (!$inventory) {
            return false;
        }

        return $inventory->reserveStock($quantity);
    }

    public function releaseStock(int $inventoryId, int $quantity): void
    {
        $inventory = $this->repository->find($inventoryId);
        
        if ($inventory) {
            $inventory->releaseStock($quantity);
        }
    }

    public function consumeStock(int $inventoryId, int $quantity): bool
    {
        $inventory = $this->repository->find($inventoryId);
        
        if (!$inventory) {
            return false;
        }

        return $inventory->consumeStock($quantity);
    }

    public function addStock(int $inventoryId, int $quantity, float $unitCost): void
    {
        $inventory = $this->repository->find($inventoryId);
        
        if ($inventory) {
            $inventory->addStock($quantity, $unitCost);
        }
    }

    public function getInventoryByLastCountDate(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('last_count_date', [$startDate, $endDate])->get();
    }

    public function getInventoryByLastReceivedDate(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('last_received_date', [$startDate, $endDate])->get();
    }
}
