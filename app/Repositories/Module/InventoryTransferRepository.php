<?php

namespace App\Repositories\Module;

use App\Models\InventoryTransfer;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class InventoryTransferRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(InventoryTransfer $inventoryTransfer)
    {
        parent::__construct($inventoryTransfer);
    }

    public function getModuleName(): string
    {
        return 'inventory_transfer';
    }

    protected function getModelClass(): string
    {
        return InventoryTransfer::class;
    }
}
