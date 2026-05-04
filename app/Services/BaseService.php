<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;
use Exception;
use Illuminate\Validation\ValidationException;

abstract class BaseService
{
    protected $repository;

    protected function logActivity(string $action, Model $model, array $data = []): void
    {
        // Only log updates, skip creates and deletes (using soft deletes)
        if ($action !== 'updated') {
            return;
        }

        $properties = ['old' => $data['old'] ?? [], 'new' => $data['new'] ?? []];

        Activity::log([
            'causedBy' => auth()->user(),
            'performedOn' => $model,
            'properties' => $properties,
            'description' => "updated " . class_basename($model),
        ]);
    }

    protected function executeInTransaction(callable $callback)
    {
        try {
            return DB::transaction($callback);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Service error: ' . $e->getMessage(), [
                'service' => static::class,
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Operation failed. Please try again.');
        }
    }

    abstract protected function getDtoClass(): string;
}
