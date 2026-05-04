<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepository extends ModelRepository
{
    public function findByEmail(string $email): ?User;
    
    public function createWithPassword(array $data): User;
}
