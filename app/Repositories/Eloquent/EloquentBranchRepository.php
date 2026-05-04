<?php

namespace App\Repositories\Eloquent;

use App\Models\Branch;
use App\Repositories\Interfaces\BranchRepository;
use App\DTOs\Branch\BranchData;

class EloquentBranchRepository extends EloquentModelRepository implements BranchRepository
{
    public function __construct(Branch $branch)
    {
        parent::__construct($branch);
    }

    public function findByCode(string $code): ?Branch
    {
        return $this->findBy('code', $code);
    }

    public function createFromData(BranchData $data): Branch
    {
        return $this->create($data->toArray());
    }

    public function updateFromData(int $id, BranchData $data): Branch
    {
        return $this->update($id, $data->toArray());
    }

    public function getActiveBranches(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('is_active', true);
    }

    public function getMainBranch(): ?Branch
    {
        return $this->findBy('is_main_branch', true);
    }
}
