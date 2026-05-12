<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentSupplierRepository extends EloquentModelRepository implements SupplierRepository
{
    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('supplier_code', 'like', "%{$s}%")
                      ->orWhere('contact_person', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('city', 'like', "%{$s}%");
                });
            })
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByName(string $name): ?Supplier
    {
        return $this->findBy('name', $name);
    }

    public function getActiveSuppliers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->where('is_active', true);
    }
}
