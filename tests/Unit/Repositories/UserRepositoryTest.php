<?php

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Spatie\Permission\Models\Role;

test('UserRepository can create user', function () {
    $repository = new UserRepository(new User());
    
    $dto = new CreateUserDTO(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    $user = $repository->create($dto);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->hasRole('cashier'))->toBeTrue();
});

test('UserRepository can find user by ID', function () {
    $user = User::factory()->create();
    $repository = new UserRepository(new User());

    $foundUser = $repository->findById($user->id);

    expect($foundUser)->not->toBeNull();
    expect($foundUser->id)->toBe($user->id);
    expect($foundUser->name)->toBe($user->name);
});

test('UserRepository can find user by email', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $repository = new UserRepository(new User());

    $foundUser = $repository->findByEmail('test@example.com');

    expect($foundUser)->not->toBeNull();
    expect($foundUser->id)->toBe($user->id);
});

test('UserRepository can update user', function () {
    $user = User::factory()->create();
    $repository = new UserRepository(new User());

    $dto = new UpdateUserDTO(
        name: 'Updated Name',
        email: 'updated@example.com',
        roles: ['store_manager'],
        userId: $user->id
    );

    $updatedUser = $repository->update($user->id, $dto);

    expect($updatedUser->name)->toBe('Updated Name');
    expect($updatedUser->email)->toBe('updated@example.com');
    expect($updatedUser->hasRole('store_manager'))->toBeTrue();
});

test('UserRepository can delete user', function () {
    $user = User::factory()->create();
    $repository = new UserRepository(new User());

    $deleted = $repository->delete($user->id);

    expect($deleted)->toBeTrue();
    expect(User::find($user->id))->toBeNull();
});

test('UserRepository can paginate with filters', function () {
    User::factory()->count(20)->create();
    $repository = new UserRepository(new User());

    $filters = new UserFiltersDTO(
        perPage: 5,
        sortBy: 'name',
        sortDirection: 'asc'
    );

    $paginatedUsers = $repository->paginateWithFilters($filters);

    expect($paginatedUsers)->toHaveProperty('total');
    expect($paginatedUsers->perPage())->toBe(5);
    expect($paginatedUsers->items())->toHaveCount(5);
});

test('UserRepository can search users', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);
    User::factory()->create(['email' => 'john@example.com']);
    
    $repository = new UserRepository(new User());

    $results = $repository->searchUsers('John');

    expect($results)->toHaveCount(2);
});

test('UserRepository can get users by role', function () {
    $cashierRole = Role::findByName('cashier');
    $adminRole = Role::findByName('admin');

    User::factory()->create()->assignRole($cashierRole);
    User::factory()->create()->assignRole($adminRole);
    User::factory()->create()->assignRole($cashierRole);
    
    $repository = new UserRepository(new User());

    $cashiers = $repository->getUsersByRole('cashier');

    expect($cashiers)->toHaveCount(2);
    $cashiers->each(function ($user) {
        expect($user->hasRole('cashier'))->toBeTrue();
    });
});

test('UserRepository checks email existence', function () {
    User::factory()->create(['email' => 'test@example.com']);
    $repository = new UserRepository(new User());

    expect($repository->emailExists('test@example.com'))->toBeTrue();
    expect($repository->emailExists('nonexistent@example.com'))->toBeFalse();
});

test('UserRepository checks email existence with exclusion', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $repository = new UserRepository(new User());

    expect($repository->emailExists('test@example.com', $user->id))->toBeFalse();
    expect($repository->emailExists('test@example.com'))->toBeTrue();
});

test('UserRepository gets user count by role', function () {
    $cashierRole = Role::findByName('cashier');
    
    User::factory()->count(3)->create()->each(function ($user) use ($cashierRole) {
        $user->assignRole($cashierRole);
    });
    
    $repository = new UserRepository(new User());

    $count = $repository->getUserCountByRole('cashier');

    expect($count)->toBe(3);
});
