<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PersonnelService extends BaseService
{
    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function listPersonnel(Request $request): LengthAwarePaginator
    {
        $filters = $request->only(['search', 'branch_id', 'role']);
        $perPage = (int) $request->input('per_page', 10);

        return $this->repository->getPersonnelPaginated($filters, $perPage);
    }

    public function assignBranch(User $user, int $branchId, ?string $role): User
    {
        $updated = null;

        $this->executeInTransaction(function () use ($user, $branchId, $role, &$updated) {
            $updated = $this->repository->assignToBranch($user->id, $branchId);
            if ($role) {
                $updated->syncRoles([$role]);
            }
        });

        return $updated->load(['branch', 'roles']);
    }

    public function revokeBranch(User $user): User
    {
        $updated = null;

        $this->executeInTransaction(function () use ($user, &$updated) {
            $updated = $this->repository->revokeFromBranch($user->id);
            $updated->syncRoles([]);
        });

        return $updated->load(['branch', 'roles']);
    }

    public function assignRole(User $user, string $role): User
    {
        $user->syncRoles([$role]);
        return $user->load(['branch', 'roles']);
    }

    public function revokeRole(User $user): User
    {
        $user->syncRoles([]);
        return $user->load(['branch', 'roles']);
    }

    public function createStaff(array $data): User
    {
        $created = null;

        $this->executeInTransaction(function () use ($data, &$created) {
            $created = $this->repository->createWithPassword([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'password'  => $data['password'],
            ]);

            if (!empty($data['role'])) {
                $created->syncRoles([$data['role']]);
            }
        });

        return $created->load(['branch', 'roles']);
    }

    public function updateStaff(User $user, array $data): User
    {
        $updated = null;

        $this->executeInTransaction(function () use ($user, $data, &$updated) {
            $updated = $this->repository->updateWithPassword($user->id, [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'password'  => $data['password'] ?? null,
            ]);

            if (!empty($data['role'])) {
                $updated->syncRoles([$data['role']]);
            } elseif (array_key_exists('role', $data) && empty($data['role'])) {
                $updated->syncRoles([]);
            }
        });

        return $updated->load(['branch', 'roles']);
    }

    public function deleteStaff(User $user): void
    {
        $this->executeInTransaction(function () use ($user) {
            $user->syncRoles([]);
            $this->repository->delete($user->id);
        });
    }

    protected function getDtoClass(): string
    {
        return '';
    }
}
