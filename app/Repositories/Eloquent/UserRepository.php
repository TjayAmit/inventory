<?php

namespace App\Repositories\Eloquent;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Get paginated users with filters.
     */
    public function paginateWithFilters(UserFiltersDTO $filters): LengthAwarePaginator
    {
        $query = $this->model->with('roles');

        // Apply search filter
        if ($filters->hasSearch()) {
            $searchTerm = $filters->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Apply role filter
        if ($filters->hasRoleFilter()) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters->role);
            });
        }

        // Apply sorting
        $sortBy = $filters->sortBy ?? 'created_at';
        $sortDirection = $filters->sortDirection ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($filters->perPage ?? 10);
    }

    /**
     * Find user by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?User
    {
        $query = $this->model;

        if (!empty($relations)) {
            $query = $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new user.
     */
    public function create(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->model->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->getHashedPassword(),
            ]);

            $user->syncRoles($dto->getRoles());

            return $user->fresh(['roles']);
        });
    }

    /**
     * Update an existing user.
     */
    public function update(int $id, UpdateUserDTO $dto): User
    {
        return DB::transaction(function () use ($id, $dto) {
            $user = $this->findById($id);
            
            if (!$user) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found');
            }

            // Update user data
            $user->update($dto->getUserData());

            // Update password if provided
            if ($dto->hasPassword()) {
                $user->update([
                    'password' => $dto->getHashedPassword(),
                ]);
            }

            // Sync roles
            $user->syncRoles($dto->getRoles());

            return $user->fresh(['roles']);
        });
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get users by role name.
     */
    public function getUsersByRole(string $roleName, int $limit = null): \Illuminate\Support\Collection
    {
        $query = $this->model->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        })->with('roles');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Search users with query and filters.
     */
    public function searchUsers(string $query, array $filters = []): \Illuminate\Support\Collection
    {
        $userQuery = $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        });

        // Apply additional filters
        if (isset($filters['role'])) {
            $userQuery->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        return $userQuery->with('roles')->get();
    }

    /**
     * Check if email exists for user (excluding specific user).
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Get user count by role.
     */
    public function getUserCountByRole(string $roleName): int
    {
        return $this->model->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        })->count();
    }

    /**
     * Get all users with relationships.
     */
    public function all(array $relations = []): \Illuminate\Support\Collection
    {
        $query = $this->model;

        if (!empty($relations)) {
            $query = $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get users created within date range.
     */
    public function getUsersCreatedBetween(\DateTime $startDate, \DateTime $endDate): \Illuminate\Support\Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->with('roles')
            ->get();
    }

    /**
     * Update user password.
     */
    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        return $this->model->where('id', $userId)->update([
            'password' => $hashedPassword,
        ]) > 0;
    }

    /**
     * Get user with roles and permissions.
     */
    public function getUserWithRolesAndPermissions(int $id): ?User
    {
        return $this->model->with(['roles', 'permissions'])->find($id);
    }
}
