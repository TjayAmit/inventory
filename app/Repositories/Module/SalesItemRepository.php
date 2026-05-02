<?php

namespace App\Repositories\Module;

use App\Models\SalesItem;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class SalesItemRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(SalesItem $salesItem)
    {
        parent::__construct($salesItem);
    }

    public function getModuleName(): string
    {
        return 'sales_item';
    }

    protected function getModelClass(): string
    {
        return SalesItem::class;
    }
}
