<?php

namespace App\Repositories\Module;

use App\Models\Inventory;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class InventoryRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);
    }

    public function getModuleName(): string
    {
        return 'inventory';
    }

    protected function getModelClass(): string
    {
        return Inventory::class;
    }
}
