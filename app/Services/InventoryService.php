<?php

namespace App\Services;

use App\Models\Inventory;
use App\Repositories\Interfaces\InventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryService extends BaseService
{
    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->repository = $inventoryRepository;
    }

    public function create(Request $request): Inventory
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

    public function update(Request $request, Inventory $inventory): Inventory
    {
        $old = $inventory->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $inventory, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($inventory->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function updateQuantity(int $id, int $quantity): Inventory
    {
        $old = null;
        $updated = null;

        $this->executeInTransaction(function () use ($id, $quantity, &$old, &$updated) {
            $inventory = $this->repository->findOrFail($id);
            $old = $inventory->getOriginal();
            $updated = $this->repository->updateQuantity($id, $quantity);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => ['quantity_on_hand' => $quantity]]);

        return $updated;
    }

    public function delete(Inventory $inventory): bool
    {
        $data = $inventory->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($inventory, &$result) {
            $result = $this->repository->delete($inventory->id);
        });

        $this->logActivity('deleted', $inventory, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
