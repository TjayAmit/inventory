<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

it('all public routes return successful responses', function () {
    // Test public routes that don't require authentication
    $publicRoutes = [
        '/',
        '/login',
        '/register',
        '/forgot-password',
    ];

    foreach ($publicRoutes as $route) {
        $response = $this->get($route);
        
        // Public routes should return 200, 302, or 419 (CSRF token)
        expect($response->status())->toBeIn([200, 302, 419]);
    }
});

it('protected routes redirect unauthenticated users', function () {
    // Test routes that require authentication
    $protectedRoutes = [
        '/dashboard',
        '/users',
        '/users/create',
        '/settings/profile',
        '/settings/security',
        '/settings/appearance',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }
});

it('authenticated users can access protected routes', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();
    $user->assignRole('admin');

    $protectedRoutes = [
        '/dashboard',
        '/users',
        '/users/create',
        '/settings/profile',
        '/settings/security',
        '/settings/appearance',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->actingAs($user)->get($route);
        $response->assertOk();
    }
});

it('API routes require authentication', function () {
    // Test users list endpoint
    $response = $this->get('/api/v1/users');
    expect($response->status())->toBeIn([302, 401]);

    // Test users search endpoint with query parameter
    $response = $this->get('/api/v1/users/search?query=test');
    expect($response->status())->toBeIn([302, 401]);

    // Test users statistics endpoint
    $response = $this->get('/api/v1/users/statistics');
    expect($response->status())->toBeIn([302, 401]);
});

it('authenticated users can access API routes', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();
    $user->assignRole('admin');

    // Create some test users for API endpoints
    User::factory()->count(5)->create();

    // Test users list endpoint
    $response = $this->actingAs($user)->get('/api/v1/users');
    $response->assertOk();

    // Test users search endpoint with query parameter
    $response = $this->actingAs($user)->get('/api/v1/users/search?query=test');
    $response->assertOk();

    // Test users statistics endpoint
    $response = $this->actingAs($user)->get('/api/v1/users/statistics');
    $response->assertOk();
});

it('authentication routes work correctly', function () {
    // Test login page
    $response = $this->get('/login');
    $response->assertOk();

    // Test registration page (if enabled)
    $response = $this->get('/register');
    $response->assertOk();

    // Test password reset page
    $response = $this->get('/forgot-password');
    $response->assertOk();
});

it('user management routes work with proper permissions', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $testUser = User::factory()->create();

    // Test user list
    $response = $this->actingAs($admin)->get('/users');
    $response->assertOk();

    // Test user create page
    $response = $this->actingAs($admin)->get('/users/create');
    $response->assertOk();

    // Test user edit page
    $response = $this->actingAs($admin)->get("/users/{$testUser->id}/edit");
    $response->assertOk();

    // Test user show page
    $response = $this->actingAs($admin)->get("/users/{$testUser->id}");
    $response->assertOk();
});

it('non-admin users cannot access user management routes', function () {
    $this->setUpRolesAndPermissions();
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');

    $managementRoutes = [
        '/users',
        '/users/create',
    ];

    foreach ($managementRoutes as $route) {
        $response = $this->actingAs($cashier)->get($route);
        $response->assertForbidden();
    }
});

it('settings routes work for authenticated users', function () {
    $this->setUpRolesAndPermissions();
    
    $user = User::factory()->create();
    $user->assignRole('cashier');

    $settingsRoutes = [
        '/settings/profile',
        '/settings/security',
        '/settings/appearance',
    ];

    foreach ($settingsRoutes as $route) {
        $response = $this->actingAs($user)->get($route);
        $response->assertOk();
    }
});

it('two-factor authentication routes exist', function () {
    // Test that two-factor routes are registered
    $twoFactorRoutes = [
        'two-factor.login',
        'password.confirm',
    ];

    foreach ($twoFactorRoutes as $routeName) {
        expect(Route::has($routeName))->toBeTrue();
    }
});

it('all named routes are accessible', function () {
    // Get all registered routes
    $routes = Route::getRoutes();
    
    $importantRouteNames = [
        'home',
        'login',
        'dashboard',
        'users.index',
        'users.create',
        'profile.edit',
        'security.edit',
        'api.users.index',
    ];

    foreach ($importantRouteNames as $routeName) {
        expect(Route::has($routeName))->toBeTrue("Route '{$routeName}' should be registered");
    }
});
