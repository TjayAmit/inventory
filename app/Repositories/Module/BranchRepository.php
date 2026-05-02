<?php

namespace App\Repositories\Module;

use App\Models\Branch;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class BranchRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Branch $branch)
    {
        parent::__construct($branch);
    }

    public function getModuleName(): string
    {
        return 'branch';
    }

    protected function getModelClass(): string
    {
        return Branch::class;
    }
}
