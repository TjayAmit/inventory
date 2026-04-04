<?php

namespace App\Repositories\Contracts;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Get paginated users with filters.
     */
    public function paginateWithFilters(UserFiltersDTO $filters): LengthAwarePaginator;

    /**
     * Find user by ID with optional relationships.
     */
    public function findById(int $id, array $relations = []): ?User;

    /**
     * Create a new user.
     */
    public function create(CreateUserDTO $dto): User;

    /**
     * Update an existing user.
     */
    public function update(int $id, UpdateUserDTO $dto): User;

    /**
     * Delete a user.
     */
    public function delete(int $id): bool;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get users by role name.
     */
    public function getUsersByRole(string $roleName, int $limit = null): \Illuminate\Support\Collection;

    /**
     * Search users with query and filters.
     */
    public function searchUsers(string $query, array $filters = []): \Illuminate\Support\Collection;

    /**
     * Check if email exists for user (excluding specific user).
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;

    /**
     * Get user count by role.
     */
    public function getUserCountByRole(string $roleName): int;

    /**
     * Get all users with relationships.
     */
    public function all(array $relations = []): \Illuminate\Support\Collection;

    /**
     * Get users created within date range.
     */
    public function getUsersCreatedBetween(\DateTime $startDate, \DateTime $endDate): \Illuminate\Support\Collection;

    /**
     * Update user password.
     */
    public function updatePassword(int $userId, string $hashedPassword): bool;

    /**
     * Get user with roles and permissions.
     */
    public function getUserWithRolesAndPermissions(int $id): ?User;
}
