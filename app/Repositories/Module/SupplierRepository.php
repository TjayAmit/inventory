<?php

namespace App\Repositories\Module;

use App\Models\Supplier;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class SupplierRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    public function getModuleName(): string
    {
        return 'supplier';
    }

    protected function getModelClass(): string
    {
        return Supplier::class;
    }
}
