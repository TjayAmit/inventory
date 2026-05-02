<?php

namespace App\Repositories\Base;

use App\Repositories\Contracts\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return $this->model::create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->find($id);
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }

        $model->update($data);
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }

        return $model->delete();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        $model = $this->find($id);
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }

        return $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function where(string $column, $operator, $value)
    {
        return $this->model->where($column, $operator, $value);
    }

    public function whereIn(string $column, array $values)
    {
        return $this->model->whereIn($column, $values);
    }

    public function orderBy(string $column, string $direction = 'asc')
    {
        return $this->model->orderBy($column, $direction);
    }

    public function with(array $relations)
    {
        return $this->model->with($relations);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function first()
    {
        return $this->model->first();
    }

    public function firstWhere(string $column, $operator, $value)
    {
        return $this->model->where($column, $operator, $value)->first();
    }

    public function exists(): bool
    {
        return $this->model->exists();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function max(string $column)
    {
        return $this->model->max($column);
    }

    public function min(string $column)
    {
        return $this->model->min($column);
    }

    public function sum(string $column)
    {
        return $this->model->sum($column);
    }

    public function avg(string $column)
    {
        return $this->model->avg($column);
    }
}
