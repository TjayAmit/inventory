<?php

namespace App\Repositories\Interfaces;

use App\Models\Branch;
use App\DTOs\Branch\BranchData;
use Illuminate\Pagination\LengthAwarePaginator;

interface BranchRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;
    public function findByCode(string $code): ?Branch;
    
    public function createFromData(BranchData $data): Branch;
    
    public function updateFromData(int $id, BranchData $data): Branch;
    
    public function getActiveBranches(): \Illuminate\Database\Eloquent\Collection;
    
    public function getMainBranch(): ?Branch;
}
