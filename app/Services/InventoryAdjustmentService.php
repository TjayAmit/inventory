<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Repositories\Interfaces\InventoryAdjustmentRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentService extends BaseService
{
    public function __construct(InventoryAdjustmentRepository $inventoryAdjustmentRepository)
    {
        $this->repository = $inventoryAdjustmentRepository;
    }

    public function create(Request $request): InventoryAdjustment
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

    public function update(Request $request, InventoryAdjustment $inventoryAdjustment): InventoryAdjustment
    {
        $old = $inventoryAdjustment->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $inventoryAdjustment, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($inventoryAdjustment->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(InventoryAdjustment $inventoryAdjustment): bool
    {
        $data = $inventoryAdjustment->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($inventoryAdjustment, &$result) {
            $result = $this->repository->delete($inventoryAdjustment->id);
        });

        $this->logActivity('deleted', $inventoryAdjustment, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
