<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Services\UserService;
use App\Services\UserRoleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

beforeEach(function () {
    $this->userService = mock(UserService::class);
    $this->roleService = mock(UserRoleService::class);
    $this->controller = new UserController($this->userService, $this->roleService);
});

it('handles InvalidArgumentException during user creation', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $this->userService->shouldReceive('createUser')
        ->andThrow(new \InvalidArgumentException('Invalid role assignment'));
    
    $request = new Request([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'roles' => ['cashier'], // Use valid role to pass validation
    ]);
    
    $response = $this->controller->store($request);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    
    // Check if the session has errors
    $sessionData = $response->getSession()->all();
    expect($sessionData)->toHaveKey('errors');
    
    // Check if errors is a ViewErrorBag instance
    $errors = $response->getSession()->get('errors');
    expect($errors)->toBeInstanceOf(\Illuminate\Support\ViewErrorBag::class);
    
    // Check for the roles field error
    expect($errors->has('roles'))->toBeTrue();
    expect($errors->get('roles')[0])->toBe('Invalid role assignment');
});

it('handles general exceptions during user creation', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $this->userService->shouldReceive('createUser')
        ->andThrow(new \Exception('Database error'));
    
    $request = new Request([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'roles' => ['cashier'],
    ]);
    
    $response = $this->controller->store($request);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    
    // Check if the session has errors
    $sessionData = $response->getSession()->all();
    
    // The session should have errors key with the error bag
    expect($sessionData)->toHaveKey('errors');
    
    // Check if errors is a ViewErrorBag instance
    $errors = $response->getSession()->get('errors');
    expect($errors)->toBeInstanceOf(\Illuminate\Support\ViewErrorBag::class);
    
    // Check for the name field error
    expect($errors->has('name'))->toBeTrue();
    expect($errors->get('name')[0])->toBe('Failed to create user. Please try again.');
});

it('handles AuthorizationException during user creation', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    // Mock the service to throw AuthorizationException
    $this->userService->shouldReceive('createUser')
        ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Cannot create admin users'));
    
    $request = new Request([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'roles' => ['admin'],
    ]);
    
    $response = $this->controller->store($request);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getSession()->get('error'))->toBe('Cannot create admin users');
});

it('handles AuthorizationException during user update', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $user = User::factory()->create();
    
    // Mock the service to throw AuthorizationException
    $this->userService->shouldReceive('updateUser')
        ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Cannot update admin users'));
    
    $request = new Request([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'roles' => ['admin'],
    ]);
    
    $response = $this->controller->update($request, $user);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getSession()->get('error'))->toBe('Cannot update admin users');
});

it('handles general exceptions during user update', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $user = User::factory()->create();
    
    $this->userService->shouldReceive('updateUser')
        ->andThrow(new \Exception('Database connection failed'));
    
    $request = new Request([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'roles' => ['cashier'],
    ]);
    
    $response = $this->controller->update($request, $user);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    
    // Check if the session has errors
    $sessionData = $response->getSession()->all();
    expect($sessionData)->toHaveKey('errors');
    
    // Check if errors is a ViewErrorBag instance
    $errors = $response->getSession()->get('errors');
    expect($errors)->toBeInstanceOf(\Illuminate\Support\ViewErrorBag::class);
    
    // Check for the name field error
    expect($errors->has('name'))->toBeTrue();
    expect($errors->get('name')[0])->toBe('Failed to update user. Please try again.');
});

it('handles AuthorizationException during user deletion', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $user = User::factory()->create();
    
    // Mock the service to throw AuthorizationException
    $this->userService->shouldReceive('deleteUser')
        ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Cannot delete admin users'));
    
    $response = $this->controller->destroy($user);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getSession()->get('error'))->toBe('Cannot delete admin users');
});

it('handles general exceptions during user deletion', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $user = User::factory()->create();
    
    $this->userService->shouldReceive('deleteUser')
        ->andThrow(new \Exception('Foreign key constraint violation'));
    
    $response = $this->controller->destroy($user);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getSession()->get('error'))->toBe('Failed to delete user. Please try again.');
});

it('handles failed user deletion with false return', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);
    
    $user = User::factory()->create();
    
    $this->userService->shouldReceive('deleteUser')->andReturn(false);
    
    $response = $this->controller->destroy($user);
    
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getSession()->get('error'))->toBe('Failed to delete user.');
});
