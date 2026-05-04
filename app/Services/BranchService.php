<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\Interfaces\BranchRepository;
use App\DTOs\Branch\BranchData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class BranchService extends BaseService
{
    public function __construct(BranchRepository $branchRepository)
    {
        $this->repository = $branchRepository;
    }

    public function create(Request $request): Branch
    {
        $model = null;
        $dto = null;

        $this->executeInTransaction(function () use ($request, &$model, &$dto) {
            $dto = BranchData::fromRequest($request);
            $model = $this->repository->createFromData($dto);
        });

        $this->logActivity('created', $model, $dto->toArray());

        return $model;
    }

    public function update(Request $request, Branch $branch): Branch
    {
        $old = $branch->getOriginal();
        $dto = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $branch, &$dto, &$updated) {
            $dto = BranchData::fromRequest($request);
            $updated = $this->repository->updateFromData($branch->id, $dto);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $dto->toArray()]);

        return $updated;
    }

    public function delete(Branch $branch): bool
    {
        $data = $branch->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($branch, &$result) {
            $result = $this->repository->delete($branch->id);
        });

        $this->logActivity('deleted', $branch, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return BranchData::class;
    }
}
