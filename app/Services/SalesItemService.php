<?php

namespace App\Services;

use App\Models\SalesItem;
use App\Repositories\Interfaces\SalesItemRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SalesItemService extends BaseService
{
    public function __construct(SalesItemRepository $salesItemRepository)
    {
        $this->repository = $salesItemRepository;
    }

    public function create(Request $request): SalesItem
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

    public function update(Request $request, SalesItem $salesItem): SalesItem
    {
        $old = $salesItem->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $salesItem, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($salesItem->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(SalesItem $salesItem): bool
    {
        $data = $salesItem->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($salesItem, &$result) {
            $result = $this->repository->delete($salesItem->id);
        });

        $this->logActivity('deleted', $salesItem, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
