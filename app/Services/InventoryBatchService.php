<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Repositories\Interfaces\InventoryBatchRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryBatchService extends BaseService
{
    public function __construct(InventoryBatchRepository $inventoryBatchRepository)
    {
        $this->repository = $inventoryBatchRepository;
    }

    public function create(Request $request): InventoryBatch
    {
        $model = null;
        $data = null;

        $this->executeInTransaction(function () use ($request, &$model, &$data) {
            $data = $request->validated();
            $model = $this->repository->create($data);
        });

        $this->logActivity('created', $model, $data);

        return $model;
    }

    public function update(Request $request, InventoryBatch $inventoryBatch): InventoryBatch
    {
        $old = $inventoryBatch->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $inventoryBatch, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($inventoryBatch->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(InventoryBatch $inventoryBatch): bool
    {
        $data = $inventoryBatch->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($inventoryBatch, &$result) {
            $result = $this->repository->delete($inventoryBatch->id);
        });

        $this->logActivity('deleted', $inventoryBatch, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
