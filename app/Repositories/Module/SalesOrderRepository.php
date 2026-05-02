<?php

namespace App\Repositories\Module;

use App\Models\SalesOrder;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class SalesOrderRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(SalesOrder $salesOrder)
    {
        parent::__construct($salesOrder);
    }

    public function getModuleName(): string
    {
        return 'sales_order';
    }

    protected function getModelClass(): string
    {
        return SalesOrder::class;
    }
}
