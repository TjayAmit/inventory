<?php

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserRoleService;
use App\Services\UserService;
use Illuminate\Auth\AuthManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Mockery as m;
use Tests\Concerns\HasRolesAndPermissions;
use Tests\Unit\Services\ServiceTestCase;

uses(ServiceTestCase::class);
uses(HasRolesAndPermissions::class);

beforeEach(function () {
    $this->setUpRolesAndPermissions();

    $this->userRepository = m::mock(UserRepositoryInterface::class);
    $this->authManager = m::mock(AuthManager::class);
    $this->roleService = m::mock(UserRoleService::class);

    $this->userService = new UserService(
        $this->userRepository,
        $this->authManager,
        $this->roleService
    );

    // Create real admin user for testing
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

afterEach(function () {
    m::close();
});

describe('getUsers', function () {
    it('returns paginated users with filters', function () {
        $filters = new UserFiltersDTO(
            search: 'test',
            role: 'admin',
            perPage: 10,
            sortBy: 'name',
            sortDirection: 'asc'
        );

        $users = User::factory()->count(3)->make();
        $expectedPaginator = new LengthAwarePaginator($users, 3, 10);

        $this->userRepository
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($expectedPaginator);

        $result = $this->userService->getUsers($filters);

        expect($result)->toBe($expectedPaginator);
    });

    it('returns paginated users with default filters', function () {
        $filters = new UserFiltersDTO();

        $users = User::factory()->count(3)->make();
        $expectedPaginator = new LengthAwarePaginator($users, 3, 15);

        $this->userRepository
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($expectedPaginator);

        $result = $this->userService->getUsers($filters);

        expect($result)->toBe($expectedPaginator);
    });
});

describe('getUserById', function () {
    it('returns user DTO when user exists', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole('cashier');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($user->id, ['roles', 'permissions'])
            ->andReturn($user);

        $result = $this->userService->getUserById($user->id);

        expect($result)->not->toBeNull();
        expect($result->id)->toBe($user->id);
        expect($result->name)->toBe('Test User');
        expect($result->email)->toBe('test@example.com');
    });

    it('returns null when user does not exist', function () {
        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(99999, ['roles', 'permissions'])
            ->andReturn(null);

        $result = $this->userService->getUserById(99999);

        expect($result)->toBeNull();
    });
});

describe('createUser', function () {
    it('creates user successfully', function () {
        $dto = new CreateUserDTO(
            name: 'New User',
            email: 'new@example.com',
            password: 'password123',
            passwordConfirmation: 'password123',
            roles: ['cashier']
        );

        $createdUser = User::factory()->create([
            'name' => 'New User',
            'email' => 'new@example.com',
        ]);
        $createdUser->assignRole('cashier');

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        $this->roleService
            ->shouldReceive('validateRoleAssignment')
            ->once()
            ->with(['cashier'], $this->admin);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($createdUser);

        Log::shouldReceive('info')->once();

        $result = $this->userService->createUser($dto);

        expect($result)->not->toBeNull();
        expect($result->id)->toBe($createdUser->id);
        expect($result->name)->toBe('New User');
    });

    it('throws exception when non-admin tries to create admin user', function () {
        $dto = new CreateUserDTO(
            name: 'Fake Admin',
            email: 'fake@example.com',
            password: 'password123',
            passwordConfirmation: 'password123',
            roles: ['admin']
        );

        $manager = User::factory()->create();
        $manager->assignRole('store_manager');

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($manager);

        expect(fn () => $this->userService->createUser($dto))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class, 'Only administrators can create admin users.');
    });

    it('allows admin to create admin user', function () {
        $dto = new CreateUserDTO(
            name: 'New Admin',
            email: 'newadmin@example.com',
            password: 'password123',
            passwordConfirmation: 'password123',
            roles: ['admin']
        );

        $createdUser = User::factory()->create([
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
        ]);
        $createdUser->assignRole('admin');

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        $this->roleService
            ->shouldReceive('validateRoleAssignment')
            ->once()
            ->with(['admin'], $this->admin);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdUser);

        Log::shouldReceive('info')->once();

        $result = $this->userService->createUser($dto);

        expect($result)->not->toBeNull();
        expect($result->name)->toBe('New Admin');
    });
});

describe('updateUser', function () {
    it('updates user successfully', function () {
        $existingUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $existingUser->assignRole('cashier');

        $dto = new UpdateUserDTO(
            name: 'Updated Name',
            email: 'updated@example.com',
            password: null,
            passwordConfirmation: null,
            roles: ['cashier'],
            userId: $existingUser->id
        );

        $updatedUser = User::factory()->create([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
        $updatedUser->assignRole('cashier');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($existingUser->id)
            ->andReturn($existingUser);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        $this->roleService
            ->shouldReceive('validateRoleAssignment')
            ->once()
            ->with(['cashier'], $this->admin, $existingUser);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($existingUser->id, $dto)
            ->andReturn($updatedUser);

        Log::shouldReceive('info')->once();

        $result = $this->userService->updateUser($existingUser->id, $dto);

        expect($result)->not->toBeNull();
        expect($result->name)->toBe('Updated Name');
        expect($result->email)->toBe('updated@example.com');
    });

    it('throws exception when updating non-existent user', function () {
        $dto = new UpdateUserDTO(
            name: 'Updated Name',
            email: 'updated@example.com',
            password: null,
            passwordConfirmation: null,
            roles: ['cashier'],
            userId: 99999
        );

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(99999)
            ->andReturn(null);

        expect(fn () => $this->userService->updateUser(99999, $dto))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('throws exception when non-admin tries to update admin user', function () {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $dto = new UpdateUserDTO(
            name: 'Trying to Update',
            email: 'test@example.com',
            password: null,
            passwordConfirmation: null,
            roles: ['admin'],
            userId: $adminUser->id
        );

        $manager = User::factory()->create();
        $manager->assignRole('store_manager');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($adminUser->id)
            ->andReturn($adminUser);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($manager);

        expect(fn () => $this->userService->updateUser($adminUser->id, $dto))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    });
});

describe('deleteUser', function () {
    it('deletes user successfully', function () {
        $user = User::factory()->create([
            'name' => 'User to Delete',
            'email' => 'delete@example.com',
        ]);
        $user->assignRole('cashier');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($user->id)
            ->andReturn($user);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        Log::shouldReceive('warning')->once();

        $result = $this->userService->deleteUser($user->id);

        expect($result)->toBeTrue();
    });

    it('returns false when user does not exist', function () {
        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $result = $this->userService->deleteUser(99999);

        expect($result)->toBeFalse();
    });

    it('throws exception when user tries to delete themselves', function () {
        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($this->admin->id)
            ->andReturn($this->admin);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        expect(fn () => $this->userService->deleteUser($this->admin->id))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class, 'You cannot delete your own account.');
    });

    it('throws exception when non-admin tries to delete admin', function () {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $manager = User::factory()->create();
        $manager->assignRole('store_manager');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($adminUser->id)
            ->andReturn($adminUser);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($manager);

        expect(fn () => $this->userService->deleteUser($adminUser->id))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class, 'Only administrators can delete admin users.');
    });

    it('allows admin to delete another admin', function () {
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with($otherAdmin->id)
            ->andReturn($otherAdmin);

        $this->authManager
            ->shouldReceive('user')
            ->andReturn($this->admin);

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with($otherAdmin->id)
            ->andReturn(true);

        Log::shouldReceive('warning')->once();

        $result = $this->userService->deleteUser($otherAdmin->id);
        expect($result)->toBeTrue();
    });
});

describe('searchUsers', function () {
    it('returns users matching search query', function () {
        $users = User::factory()->count(3)->create();

        $this->userRepository
            ->shouldReceive('searchUsers')
            ->once()
            ->with('john', [])
            ->andReturn($users);

        $result = $this->userService->searchUsers('john');

        expect($result)->toBeArray();
        expect($result)->toHaveCount(3);
    });

    it('returns users with additional filters', function () {
        $users = User::factory()->count(2)->create();

        $this->userRepository
            ->shouldReceive('searchUsers')
            ->once()
            ->with('jane', ['role' => 'cashier'])
            ->andReturn($users);

        $result = $this->userService->searchUsers('jane', ['role' => 'cashier']);

        expect($result)->toHaveCount(2);
    });
});

describe('getUsersByRole', function () {
    it('returns users by role without limit', function () {
        $users = User::factory()->count(5)->create();

        $this->userRepository
            ->shouldReceive('getUsersByRole')
            ->once()
            ->with('cashier', null)
            ->andReturn($users);

        $result = $this->userService->getUsersByRole('cashier');

        expect($result)->toBeArray();
        expect($result)->toHaveCount(5);
    });

    it('returns users by role with limit', function () {
        $users = User::factory()->count(3)->create();

        $this->userRepository
            ->shouldReceive('getUsersByRole')
            ->once()
            ->with('admin', 3)
            ->andReturn($users);

        $result = $this->userService->getUsersByRole('admin', 3);

        expect($result)->toHaveCount(3);
    });
});

describe('getUserStatistics', function () {
    it('returns user statistics', function () {
        $allUsers = User::factory()->count(10)->make();

        $this->userRepository
            ->shouldReceive('all')
            ->once()
            ->andReturn($allUsers);

        $this->userRepository
            ->shouldReceive('getUserCountByRole')
            ->once()
            ->with('admin')
            ->andReturn(2);

        $this->userRepository
            ->shouldReceive('getUserCountByRole')
            ->once()
            ->with('store_manager')
            ->andReturn(3);

        $this->userRepository
            ->shouldReceive('getUserCountByRole')
            ->once()
            ->with('cashier')
            ->andReturn(5);

        $result = $this->userService->getUserStatistics();

        expect($result)->toBeArray();
        expect($result)->toHaveKey('total_users');
        expect($result)->toHaveKey('admin_count');
        expect($result)->toHaveKey('store_manager_count');
        expect($result)->toHaveKey('cashier_count');
        expect($result['total_users'])->toBe(10);
        expect($result['admin_count'])->toBe(2);
        expect($result['store_manager_count'])->toBe(3);
        expect($result['cashier_count'])->toBe(5);
    });
});
