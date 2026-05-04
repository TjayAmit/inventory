<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Repositories\Interfaces\SalesOrderRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SalesOrderService extends BaseService
{
    public function __construct(SalesOrderRepository $salesOrderRepository)
    {
        $this->repository = $salesOrderRepository;
    }

    public function create(Request $request): SalesOrder
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

    public function update(Request $request, SalesOrder $salesOrder): SalesOrder
    {
        $old = $salesOrder->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $salesOrder, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($salesOrder->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(SalesOrder $salesOrder): bool
    {
        $data = $salesOrder->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($salesOrder, &$result) {
            $result = $this->repository->delete($salesOrder->id);
        });

        $this->logActivity('deleted', $salesOrder, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
