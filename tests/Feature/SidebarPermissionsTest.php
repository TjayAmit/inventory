<?php

use App\Models\User;
use Inertia\Testing\Assert;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    // Seed permissions and roles
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(RolePermissionSeeder::class);
});

it('renders sidebar with permissions for admin user', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertInertia(function ($page) {
        $page->component('dashboard')
            ->has('auth.user')
            ->has('auth.permissions');
    });
});

it('renders sidebar with limited permissions for cashier user', function () {
    $user = User::factory()->create();
    $user->assignRole('cashier');

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertInertia(function ($page) {
        $page->component('dashboard')
            ->has('auth.user')
            ->has('auth.permissions');
    });
});

it('renders sidebar without permissions for unauthenticated user', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});
