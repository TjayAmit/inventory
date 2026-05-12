<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierService extends BaseService
{
    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->repository = $supplierRepository;
    }

    public function list(Request $request): LengthAwarePaginator
    {
        return $this->repository->getPaginated(
            $request->only(['search', 'is_active']),
            (int) $request->input('per_page', 10)
        );
    }

    public function create(Request $request): Supplier
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

    public function update(Request $request, Supplier $supplier): Supplier
    {
        $old = $supplier->getOriginal();
        $data = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $supplier, &$data, &$updated) {
            $data = $request->validated();
            $updated = $this->repository->update($supplier->id, $data);
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $data]);

        return $updated;
    }

    public function delete(Supplier $supplier): bool
    {
        $data = $supplier->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($supplier, &$result) {
            $result = $this->repository->delete($supplier->id);
        });

        $this->logActivity('deleted', $supplier, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
