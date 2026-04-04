<?php

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use App\DTOs\User\UserFiltersDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

beforeEach(function () {
    $this->repository = new UserRepository(new User());
});

it('applies search filter correctly', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    User::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);
    
    $filters = new UserFiltersDTO(
        search: 'John'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(2); // John Doe and Bob Johnson (contains 'John' in name)
    expect($result->pluck('name'))->toContain('John Doe');
    expect($result->pluck('name'))->toContain('Bob Johnson');
});

it('applies email search filter correctly', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    User::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);
    
    $filters = new UserFiltersDTO(
        search: 'jane@example.com'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(1);
    expect($result->first()->email)->toBe('jane@example.com');
});

it('applies role filter correctly', function () {
    $this->setUpRolesAndPermissions();
    
    $adminRole = \Spatie\Permission\Models\Role::findByName('admin');
    $cashierRole = \Spatie\Permission\Models\Role::findByName('cashier');
    
    // Create test users with different roles
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    
    $cashier1 = User::factory()->create();
    $cashier1->assignRole($cashierRole);
    
    $cashier2 = User::factory()->create();
    $cashier2->assignRole($cashierRole);
    
    $filters = new UserFiltersDTO(
        role: 'cashier'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(2);
    expect($result->pluck('id'))->toContain($cashier1->id, $cashier2->id);
    expect($result->pluck('id'))->not->toContain($admin->id);
});

it('applies both search and role filters correctly', function () {
    $this->setUpRolesAndPermissions();
    
    $adminRole = \Spatie\Permission\Models\Role::findByName('admin');
    $cashierRole = \Spatie\Permission\Models\Role::findByName('cashier');
    
    // Create test users
    $admin = User::factory()->create(['name' => 'John Admin']);
    $admin->assignRole($adminRole);
    
    $cashier1 = User::factory()->create(['name' => 'John Cashier']);
    $cashier1->assignRole($cashierRole);
    
    $cashier2 = User::factory()->create(['name' => 'Jane Cashier']);
    $cashier2->assignRole($cashierRole);
    
    $filters = new UserFiltersDTO(
        search: 'John',
        role: 'cashier'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(1);
    expect($result->first()->id)->toBe($cashier1->id);
});

it('returns all users when no filters applied', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->count(5)->create();
    
    $filters = new UserFiltersDTO();
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(5);
});

it('applies sorting correctly', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users with specific names
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);
    User::factory()->create(['name' => 'Charlie']);
    
    $filters = new UserFiltersDTO(
        sortBy: 'name',
        sortDirection: 'desc'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result->pluck('name')->first())->toBe('Charlie');
});

it('applies pagination correctly', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->count(25)->create();
    
    $filters = new UserFiltersDTO(
        perPage: 10
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(10);
    expect($result->perPage())->toBe(10);
});

it('searches users by partial name match', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->create(['name' => 'Christopher Robin']);
    User::factory()->create(['name' => 'Robin Hood']);
    User::factory()->create(['name' => 'Batman']);
    
    $filters = new UserFiltersDTO(
        search: 'Robin'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(2);
    expect($result->pluck('name'))->toContain('Christopher Robin', 'Robin Hood');
});

it('handles empty search results', function () {
    $this->setUpRolesAndPermissions();
    
    // Create test users
    User::factory()->count(5)->create();
    
    $filters = new UserFiltersDTO(
        search: 'NonExistentName'
    );
    
    $result = $this->repository->paginateWithFilters($filters);
    
    expect($result)->toHaveCount(0);
});
