<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Repositories\Interfaces\SalesOrderRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesOrderService extends BaseService
{
    public function __construct(SalesOrderRepository $salesOrderRepository)
    {
        $this->repository = $salesOrderRepository;
    }

    public function list(Request $request): LengthAwarePaginator
    {
        return $this->repository->getPaginated(
            $request->only(['search', 'status', 'payment_status', 'branch_id']),
            (int) $request->input('per_page', 10)
        );
    }

    public function create(Request $request): SalesOrder
    {
        $model = null;
        $data = null;

        $this->executeInTransaction(function () use ($request, &$model, &$data) {
            $data = $request->validated();
            $data['order_number'] = $this->generateOrderNumber();
            $model = $this->repository->create($data);
        });

        $this->logActivity('created', $model, $data);

        return $model;
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'SO';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
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
