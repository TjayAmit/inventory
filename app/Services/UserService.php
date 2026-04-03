<?php

namespace App\Services;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\DTOs\User\UserResponseDTO;
use App\DTOs\Transformers\UserTransformer;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected AuthManager $auth;
    protected UserRoleService $roleService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AuthManager $auth,
        UserRoleService $roleService
    ) {
        $this->userRepository = $userRepository;
        $this->auth = $auth;
        $this->roleService = $roleService;
    }

    /**
     * Get paginated users with filters.
     */
    public function getUsers(UserFiltersDTO $filters): LengthAwarePaginator
    {
        return $this->userRepository->paginateWithFilters($filters);
    }

    /**
     * Get user by ID.
     */
    public function getUserById(int $id): ?UserResponseDTO
    {
        $user = $this->userRepository->findById($id, ['roles', 'permissions']);
        
        if (!$user) {
            return null;
        }

        return UserTransformer::toResponseDTO($user);
    }

    /**
     * Create a new user.
     */
    public function createUser(CreateUserDTO $dto): UserResponseDTO
    {
        // Business rule: Non-admin users cannot create admin users
        $this->validateAdminRoleCreation($dto);

        // Validate business rules
        $this->roleService->validateRoleAssignment($dto->getRoles(), $this->getCurrentUser());

        $user = $this->userRepository->create($dto);

        // Log the user creation
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $dto->getRoles(),
            'created_by' => $this->getCurrentUser()?->id,
        ]);

        return UserTransformer::toResponseDTO($user);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $id, UpdateUserDTO $dto): UserResponseDTO
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found');
        }

        // Business rule validation
        $this->validateUserUpdate($user, $dto);

        // Validate role assignment
        $this->roleService->validateRoleAssignment($dto->getRoles(), $this->getCurrentUser(), $user);

        $updatedUser = $this->userRepository->update($id, $dto);

        // Log the user update
        Log::info('User updated', [
            'user_id' => $updatedUser->id,
            'changes' => $dto->getUserData(),
            'roles' => $dto->getRoles(),
            'updated_by' => $this->getCurrentUser()?->id,
        ]);

        return UserTransformer::toResponseDTO($updatedUser);
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return false;
        }

        // Business rule: Cannot delete admin users unless current user is admin
        $this->validateUserDeletion($user);

        $deleted = $this->userRepository->delete($id);

        if ($deleted) {
            // Log the user deletion
            Log::warning('User deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => $this->getCurrentUser()?->id,
            ]);
        }

        return $deleted;
    }

    /**
     * Search users.
     */
    public function searchUsers(string $query, array $filters = []): array
    {
        $users = $this->userRepository->searchUsers($query, $filters);
        
        return UserTransformer::toResponseDTOCollection($users);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName, int $limit = null): array
    {
        $users = $this->userRepository->getUsersByRole($roleName, $limit);
        
        return UserTransformer::toResponseDTOCollection($users);
    }

    /**
     * Get current authenticated user.
     */
    protected function getCurrentUser(): ?User
    {
        return $this->auth->user();
    }

    /**
     * Validate admin role creation.
     */
    protected function validateAdminRoleCreation(CreateUserDTO $dto): void
    {
        if ($dto->requestsAdminRole() && !$this->getCurrentUser()?->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can create admin users.');
        }
    }

    /**
     * Validate user update permissions.
     */
    protected function validateUserUpdate(User $user, UpdateUserDTO $dto): void
    {
        // Non-admin users cannot update admin users
        if ($user->hasRole('admin') && !$this->getCurrentUser()?->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can update admin users.');
        }

        // Users cannot update their own role to admin unless they are admin
        if ($user->id === $this->getCurrentUser()?->id && $dto->requestsAdminRole() && !$this->getCurrentUser()?->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You cannot assign admin role to yourself.');
        }
    }

    /**
     * Validate user deletion.
     */
    protected function validateUserDeletion(User $user): void
    {
        // Cannot delete admin users unless current user is admin
        if ($user->hasRole('admin') && !$this->getCurrentUser()?->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can delete admin users.');
        }

        // Users cannot delete themselves
        if ($user->id === $this->getCurrentUser()?->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You cannot delete your own account.');
        }
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => $this->userRepository->all()->count(),
            'admin_count' => $this->userRepository->getUserCountByRole('admin'),
            'store_manager_count' => $this->userRepository->getUserCountByRole('store_manager'),
            'cashier_count' => $this->userRepository->getUserCountByRole('cashier'),
        ];
    }
}
