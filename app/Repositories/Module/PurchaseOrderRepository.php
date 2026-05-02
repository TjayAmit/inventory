<?php

namespace App\Repositories\Module;

use App\Models\PurchaseOrder;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class PurchaseOrderRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        parent::__construct($purchaseOrder);
    }

    public function getModuleName(): string
    {
        return 'purchase_order';
    }

    protected function getModelClass(): string
    {
        return PurchaseOrder::class;
    }
}
