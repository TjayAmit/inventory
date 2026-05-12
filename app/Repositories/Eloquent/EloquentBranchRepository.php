<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Branch\BranchData;
use App\Models\Branch;
use App\Repositories\Interfaces\BranchRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentBranchRepository extends EloquentModelRepository implements BranchRepository
{
    public function __construct(Branch $branch)
    {
        parent::__construct($branch);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('manager')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('code', 'like', "%{$s}%")
                      ->orWhere('city', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
