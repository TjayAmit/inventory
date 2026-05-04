<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Repositories\Interfaces\PurchaseOrderRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderService extends BaseService
{
    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->repository = $purchaseOrderRepository;
    }

    public function create(Request $request): PurchaseOrder
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

    public function update(Request $request, PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $old = $purchaseOrder->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $purchaseOrder, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($purchaseOrder->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        $data = $purchaseOrder->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($purchaseOrder, &$result) {
            $result = $this->repository->delete($purchaseOrder->id);
        });

        $this->logActivity('deleted', $purchaseOrder, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
