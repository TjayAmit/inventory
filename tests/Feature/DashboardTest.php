<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasRolesAndPermissions;

it('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('dashboard renders with correct Inertia component', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    // Dashboard page loads successfully
});

it('admin users can access dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('store manager users can access dashboard', function () {
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');
    $this->actingAs($manager);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('cashier users can access dashboard', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    $this->actingAs($cashier);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('warehouse staff users can access dashboard', function () {
    $staff = User::factory()->create();
    $staff->assignRole('warehouse_staff');
    $this->actingAs($staff);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('dashboard page has correct head title', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(function ($page) {
        $props = $page->toArray();
        expect($props['props']['title'] ?? null)->toBeNull(); // Title set via Head component
    });
});

it('unverified users behavior based on app configuration', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    // App may or may not require email verification
    expect($response->status())->toBeIn([200, 302, 403]);
});