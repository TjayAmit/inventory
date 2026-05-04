<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\ModelRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentModelRepository implements ModelRepository
{
    protected Model $model;
    protected Builder $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }

    public function all(): Collection
    {
        return $this->query->get();
    }

    public function find(int $id): ?Model
    {
        return $this->query->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->query->findOrFail($id);
    }

    public function findBy(string $column, mixed $value): ?Model
    {
        return $this->query->where($column, $value)->first();
    }

    public function where(string $column, mixed $operator, mixed $value = null): Collection
    {
        $this->query->where($column, $operator, $value);
        return $this->query->get();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }

    public function with(array $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    public function whereHas(string $relation, callable $callback): self
    {
        $this->query->whereHas($relation, $callback);
        return $this;
    }

    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }
}
