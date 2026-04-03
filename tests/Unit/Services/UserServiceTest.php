<?php

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use App\Services\UserRoleService;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Log;

test('UserService can create user', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $dto = new CreateUserDTO(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    $mockUser = User::factory()->make(['id' => 1]);
    $userRepository->shouldReceive('create')->once()->with($dto)->andReturn($mockUser);
    $authManager->shouldReceive('user')->andReturn(null);
    $roleService->shouldReceive('validateRoleAssignment')->once();

    $result = $userService->createUser($dto);

    expect($result)->not->toBeNull();
});

test('UserService prevents non-admin from creating admin user', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $dto = new CreateUserDTO(
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['admin']
    );

    $currentUser = User::factory()->make();
    $currentUser->assignRole('cashier');
    
    $authManager->shouldReceive('user')->andReturn($currentUser);
    $roleService->shouldReceive('validateRoleAssignment')->once()->andThrow(new \Illuminate\Auth\Access\AuthorizationException());

    expect(fn() => $userService->createUser($dto))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

test('UserService can update user', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $dto = new UpdateUserDTO(
        name: 'Updated Name',
        email: 'updated@example.com',
        roles: ['store_manager'],
        userId: 1
    );

    $mockUser = User::factory()->make(['id' => 1]);
    $userRepository->shouldReceive('findById')->once()->with(1)->andReturn($mockUser);
    $userRepository->shouldReceive('update')->once()->with(1, $dto)->andReturn($mockUser);
    $authManager->shouldReceive('user')->andReturn(null);
    $roleService->shouldReceive('validateRoleAssignment')->once();

    $result = $userService->updateUser(1, $dto);

    expect($result)->not->toBeNull();
});

test('UserService can delete user', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $mockUser = User::factory()->make(['id' => 1]);
    $userRepository->shouldReceive('findById')->once()->with(1)->andReturn($mockUser);
    $userRepository->shouldReceive('delete')->once()->with(1)->andReturn(true);
    $authManager->shouldReceive('user')->andReturn(null);

    $result = $userService->deleteUser(1);

    expect($result)->toBeTrue();
});

test('UserService prevents deleting admin users', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $mockUser = User::factory()->make(['id' => 1]);
    $mockUser->assignRole('admin');
    
    $currentUser = User::factory()->make();
    $currentUser->assignRole('cashier');

    $userRepository->shouldReceive('findById')->once()->with(1)->andReturn($mockUser);
    $authManager->shouldReceive('user')->andReturn($currentUser);

    expect(fn() => $userService->deleteUser(1))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

test('UserService can get users with filters', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $filters = new UserFiltersDTO(
        search: 'test',
        perPage: 10
    );

    $mockPaginatedUsers = mock(\Illuminate\Pagination\LengthAwarePaginator::class);
    $userRepository->shouldReceive('paginateWithFilters')->once()->with($filters)->andReturn($mockPaginatedUsers);

    $result = $userService->getUsers($filters);

    expect($result)->toBe($mockPaginatedUsers);
});

test('UserService can get user by ID', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $mockUser = User::factory()->make(['id' => 1]);
    $userRepository->shouldReceive('findById')->once()->with(1, ['roles', 'permissions'])->andReturn($mockUser);

    $result = $userService->getUserById(1);

    expect($result)->not->BeNull();
});

test('UserService returns null for non-existent user', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $userRepository->shouldReceive('findById')->once()->with(999, ['roles', 'permissions'])->andReturn(null);

    $result = $userService->getUserById(999);

    expect($result)->toBeNull();
});

test('UserService can search users', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $mockUsers = collect([User::factory()->make()]);
    $userRepository->shouldReceive('searchUsers')->once()->with('test', [])->andReturn($mockUsers);

    $result = $userService->searchUsers('test');

    expect($result)->toBeArray();
    expect(count($result))->toBe(1);
});

test('UserService can get users by role', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $mockUsers = collect([User::factory()->make()]);
    $userRepository->shouldReceive('getUsersByRole')->once()->with('cashier', null)->andReturn($mockUsers);

    $result = $userService->getUsersByRole('cashier');

    expect($result)->toBeArray();
    expect(count($result))->toBe(1);
});

test('UserService can get user statistics', function () {
    $userRepository = mock(UserRepositoryInterface::class);
    $authManager = mock(AuthManager::class);
    $roleService = mock(UserRoleService::class);
    
    $userService = new UserService($userRepository, $authManager, $roleService);

    $userRepository->shouldReceive('all')->once()->andReturn(collect([User::factory()->make(), User::factory()->make()]));
    $userRepository->shouldReceive('getUserCountByRole')->with('admin')->andReturn(1);
    $userRepository->shouldReceive('getUserCountByRole')->with('store_manager')->andReturn(1);
    $userRepository->shouldReceive('getUserCountByRole')->with('cashier')->andReturn(0);

    $statistics = $userService->getUserStatistics();

    expect($statistics)->toHaveKey('total_users');
    expect($statistics)->toHaveKey('admin_count');
    expect($statistics)->toHaveKey('store_manager_count');
    expect($statistics)->toHaveKey('cashier_count');
    expect($statistics['total_users'])->toBe(2);
    expect($statistics['admin_count'])->toBe(1);
});
