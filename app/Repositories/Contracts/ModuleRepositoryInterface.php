<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ModuleRepositoryInterface
{
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function delete(int $id): bool;
    public function find(int $id): ?Model;
    public function findOrFail(int $id): Model;
    public function all();
    public function where(string $column, $operator, $value);
    public function whereIn(string $column, array $values);
    public function orderBy(string $column, string $direction = 'asc');
    public function with(array $relations);
    public function paginate(int $perPage = 15);
    public function first();
    public function firstWhere(string $column, $operator, $value);
    public function exists(): bool;
    public function count(): int;
    public function max(string $column);
    public function min(string $column);
    public function sum(string $column);
    public function avg(string $column);
}
