<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelRepository
{
    public function all(): Collection;
    
    public function find(int $id): ?Model;
    
    public function findOrFail(int $id): Model;
    
    public function findBy(string $column, mixed $value): ?Model;
    
    public function where(string $column, mixed $operator, mixed $value = null): Collection;
    
    public function create(array $data): Model;
    
    public function update(int $id, array $data): Model;
    
    public function delete(int $id): bool;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function with(array $relations): self;
    
    public function orderBy(string $column, string $direction = 'asc'): self;
    
    public function limit(int $limit): self;
    
    public function whereHas(string $relation, callable $callback): self;
}
