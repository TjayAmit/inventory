<?php

namespace App\Repositories\Module;

use App\Models\InventoryAdjustment;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class InventoryAdjustmentRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(InventoryAdjustment $inventoryAdjustment)
    {
        parent::__construct($inventoryAdjustment);
    }

    public function getModuleName(): string
    {
        return 'inventory_adjustment';
    }

    protected function getModelClass(): string
    {
        return InventoryAdjustment::class;
    }
}
