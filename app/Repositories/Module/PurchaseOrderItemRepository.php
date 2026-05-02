<?php

namespace App\Repositories\Module;

use App\Models\PurchaseOrderItem;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class PurchaseOrderItemRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(PurchaseOrderItem $purchaseOrderItem)
    {
        parent::__construct($purchaseOrderItem);
    }

    public function getModuleName(): string
    {
        return 'purchase_order_item';
    }

    protected function getModelClass(): string
    {
        return PurchaseOrderItem::class;
    }
}
