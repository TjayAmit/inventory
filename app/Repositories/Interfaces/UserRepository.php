<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepository extends ModelRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findByEmail(string $email): ?User;

    public function createWithPassword(array $data): User;

    public function updateWithPassword(int $id, array $data): User;

    public function getPersonnelPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function assignToBranch(int $userId, int $branchId): User;

    public function revokeFromBranch(int $userId): User;
}
