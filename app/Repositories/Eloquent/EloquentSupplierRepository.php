<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepository;

class EloquentSupplierRepository extends EloquentModelRepository implements SupplierRepository
{
    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
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
