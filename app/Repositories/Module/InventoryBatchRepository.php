<?php

namespace App\Repositories\Module;

use App\Models\InventoryBatch;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class InventoryBatchRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(InventoryBatch $inventoryBatch)
    {
        parent::__construct($inventoryBatch);
    }

    public function getModuleName(): string
    {
        return 'inventory_batch';
    }

    protected function getModelClass(): string
    {
        return InventoryBatch::class;
    }
}
