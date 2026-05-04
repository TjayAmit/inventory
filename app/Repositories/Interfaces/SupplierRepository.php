<?php

namespace App\Repositories\Interfaces;

use App\Models\Supplier;

interface SupplierRepository extends ModelRepository
{
    public function findByName(string $name): ?Supplier;
    
    public function getActiveSuppliers(): \Illuminate\Database\Eloquent\Collection;
}
