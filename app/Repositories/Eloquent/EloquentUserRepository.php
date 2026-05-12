<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository extends EloquentModelRepository implements UserRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    public function createWithPassword(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $model = $this->create($data);
        assert($model instanceof User);
        return $model;
    }

    public function updateWithPassword(int $id, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $model = $this->update($id, $data);
        assert($model instanceof User);
        return $model;
    }

    public function getPersonnelPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['branch', 'roles']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function assignToBranch(int $userId, int $branchId): User
    {
        $user = $this->findOrFail($userId);
        assert($user instanceof User);
        $user->update(['branch_id' => $branchId]);
        return $user;
    }

    public function revokeFromBranch(int $userId): User
    {
        $user = $this->findOrFail($userId);
        assert($user instanceof User);
        $user->update(['branch_id' => null]);
        return $user;
    }
}
