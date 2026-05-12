<?php

namespace App\Repositories\Interfaces;

use App\Models\Supplier;
use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findByName(string $name): ?Supplier;

    public function getActiveSuppliers(): \Illuminate\Database\Eloquent\Collection;
}
