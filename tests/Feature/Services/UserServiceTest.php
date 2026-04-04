<?php

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use App\Services\UserService;
use App\Services\UserRoleService;
use Illuminate\Auth\AuthManager;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

beforeEach(function () {
    $this->userRepository = new UserRepository(new User());
    $this->authManager = app(AuthManager::class);
    $this->roleService = new UserRoleService();
    $this->userService = new UserService($this->userRepository, $this->authManager, $this->roleService);
});

it('can create user', function () {
    $this->setUpRolesAndPermissions();
    
    $dto = new CreateUserDTO(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    $result = $this->userService->createUser($dto);

    expect($result)->toBeInstanceOf(\App\DTOs\User\UserResponseDTO::class);
    expect($result->name)->toBe('Test User');
    expect($result->email)->toBe('test@example.com');
    expect($result->roles)->toContain('cashier');
});

it('prevents non-admin from creating admin user', function () {
    $this->setUpRolesAndPermissions();
    
    $currentUser = User::factory()->create();
    $currentUser->assignRole('cashier');
    
    $this->actingAs($currentUser);
    
    $dto = new CreateUserDTO(
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['admin']
    );

    expect(fn() => $this->userService->createUser($dto))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

it('can update user', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();
    
    $dto = new UpdateUserDTO(
        name: 'Updated Name',
        email: 'updated@example.com',
        roles: ['store_manager'],
        userId: $user->id
    );

    $result = $this->userService->updateUser($user->id, $dto);

    expect($result)->toBeInstanceOf(\App\DTOs\User\UserResponseDTO::class);
    expect($result->name)->toBe('Updated Name');
    expect($result->email)->toBe('updated@example.com');
    expect($result->roles)->toContain('store_manager');
});

it('can delete user', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();

    $result = $this->userService->deleteUser($user->id);

    expect($result)->toBeTrue();
    expect(User::find($user->id))->toBeNull();
});

it('prevents deleting admin users', function () {
    $this->setUpRolesAndPermissions();
    
    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');
    
    $currentUser = User::factory()->create();
    $currentUser->assignRole('cashier');
    
    $this->actingAs($currentUser);

    expect(fn() => $this->userService->deleteUser($adminUser->id))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

it('can get users with filters', function () {
    $this->setUpRolesAndPermissions();
    
    User::factory()->count(20)->create();

    $filters = new UserFiltersDTO(
        perPage: 5,
        sortBy: 'name',
        sortDirection: 'asc'
    );

    $result = $this->userService->getUsers($filters);

    expect($result)->toHaveProperty('total');
    expect($result->perPage())->toBe(5);
    expect($result->items())->toHaveCount(5);
});

it('can get user by ID', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();

    $result = $this->userService->getUserById($user->id);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($user->id);
    expect($result->name)->toBe($user->name);
});

it('returns null for non-existent user', function () {
    $this->setUpRolesAndPermissions();

    $result = $this->userService->getUserById(999);

    expect($result)->toBeNull();
});

it('can search users', function () {
    $this->setUpRolesAndPermissions();
    
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);
    User::factory()->create(['email' => 'john@example.com']);

    $result = $this->userService->searchUsers('John');

    expect($result)->toBeArray();
    expect(count($result))->toBe(2);
});

it('can get users by role', function () {
    $this->setUpRolesAndPermissions();
    
    $cashierRole = \Spatie\Permission\Models\Role::findByName('cashier');
    $adminRole = \Spatie\Permission\Models\Role::findByName('admin');

    User::factory()->create()->assignRole($cashierRole);
    User::factory()->create()->assignRole($adminRole);
    User::factory()->create()->assignRole($cashierRole);

    $result = $this->userService->getUsersByRole('cashier');

    expect($result)->toBeArray();
    expect(count($result))->toBe(2);
});

it('can get user statistics', function () {
    $this->setUpRolesAndPermissions();
    
    User::factory()->count(5)->create();

    $statistics = $this->userService->getUserStatistics();

    expect($statistics)->toHaveKey('total_users');
    expect($statistics)->toHaveKey('admin_count');
    expect($statistics)->toHaveKey('store_manager_count');
    expect($statistics)->toHaveKey('cashier_count');
    expect($statistics['total_users'])->toBe(5);
});
