<?php

namespace App\Services;

use App\Models\PurchaseOrderItem;
use App\Repositories\Interfaces\PurchaseOrderItemRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItemService extends BaseService
{
    public function __construct(PurchaseOrderItemRepository $purchaseOrderItemRepository)
    {
        $this->repository = $purchaseOrderItemRepository;
    }

    public function create(Request $request): PurchaseOrderItem
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

    public function update(Request $request, PurchaseOrderItem $purchaseOrderItem): PurchaseOrderItem
    {
        $old = $purchaseOrderItem->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $purchaseOrderItem, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($purchaseOrderItem->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(PurchaseOrderItem $purchaseOrderItem): bool
    {
        $data = $purchaseOrderItem->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($purchaseOrderItem, &$result) {
            $result = $this->repository->delete($purchaseOrderItem->id);
        });

        $this->logActivity('deleted', $purchaseOrderItem, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
