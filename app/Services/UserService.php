<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function create(Request $request): User
    {
        $model = null;
        $dto = null;

        $this->executeInTransaction(function () use ($request, &$model, &$dto) {
            $model = $this->repository->createWithPassword($request->validated());
            $dto = $request->validated();
        });

        $this->logActivity('created', $model, $dto);

        return $model;
    }

    public function update(Request $request, User $user): User
    {
        $old = $user->getOriginal();
        $dto = null;
        $updated = null;

        $this->executeInTransaction(function () use ($request, $user, &$dto, &$updated) {
            $data = $request->validated();
            
            if (isset($data['password'])) {
                unset($data['password']);
            }
            
            $updated = $this->repository->update($user->id, $data);
            $dto = $data;
        });

        $this->logActivity('updated', $updated, ['old' => $old, 'new' => $dto]);

        return $updated;
    }

    public function delete(User $user): bool
    {
        $data = $user->toArray();
        $result = false;

        $this->executeInTransaction(function () use ($user, &$result) {
            $result = $this->repository->delete($user->id);
        });

        $this->logActivity('deleted', $user, $data);

        return $result;
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
