<?php

namespace App\Services\Base;

use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

abstract class BaseService
{
    public function __construct(
        protected ModuleRepositoryInterface $repository
    ) {}

    public function create(Request $request): Model
    {
        $model = null;
        $dto = null;
        
        DB::transaction(function () use ($request, &$model, &$dto) {
            $dto = $this->getDtoClass()::fromRequest($request);
            $model = $this->repository->create($dto->toArray());
        });
        
        // Log activity AFTER transaction commits to ensure data integrity
        // This follows Laravel's best practice for events within transactions
        $this->logActivity('created', $model, $dto->toArray());
        
        return $model;
    }

    public function update(Request $request, Model $model): Model
    {
        $oldData = $model->getOriginal();
        $dto = null;
        $updatedModel = null;
        
        DB::transaction(function () use ($request, $model, &$dto, &$updatedModel) {
            $dto = $this->getDtoClass()::fromRequest($request);
            $updatedModel = $this->repository->update($model->id, $dto->toArray());
        });
        
        // Log activity AFTER transaction commits
        $this->logActivity('updated', $updatedModel, [
            'old' => $oldData,
            'new' => $dto->toArray()
        ]);
        
        return $updatedModel;
    }

    public function delete(Model $model): bool
    {
        $data = $model->toArray();
        $result = false;
        
        DB::transaction(function () use ($model, &$result) {
            $result = $this->repository->delete($model->id);
        });
        
        // Log activity AFTER transaction commits
        $this->logActivity('deleted', $model, $data);
        
        return $result;
    }

    abstract protected function getDtoClass(): string;
    
    protected function logActivity(string $action, Model $model, array $data = []): void
    {
        $properties = [];
        
        if ($action === 'updated') {
            $properties['old'] = $model->getOriginal();
            $properties['new'] = $data;
        }
        
        if ($action === 'deleted') {
            $properties['deleted_data'] = $data;
            $properties['deleted_by'] = Auth::id();
        }
        
        activity()
            ->causedBy(Auth::user())
            ->performedOn($model)
            ->withProperties($properties)
            ->log("{$action} " . class_basename($model));
    }
}
