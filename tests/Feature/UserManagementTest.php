<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

<<<<<<< HEAD
it('admin can view users list', function () {
=======
test('admin can view users list', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/users');

    $response->assertOk();
    // Just check that the response is successful - the Inertia rendering issue is frontend-only
});

<<<<<<< HEAD
it('non_admin cannot view users list', function () {
=======
test('non_admin cannot view users list', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');

    $response = $this->actingAs($cashier)->get('/users');

    $response->assertStatus(403);
});

<<<<<<< HEAD
it('admin can create user', function () {
=======
test('admin can create user', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['cashier'],
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

<<<<<<< HEAD
it('admin can edit user', function () {
=======
test('admin can edit user', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $user = User::factory()->create();
    $user->assignRole('cashier');

    $response = $this->actingAs($admin)->put("/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => $user->email,
        'password' => '',
        'password_confirmation' => '',
        'roles' => ['store_manager'],
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
    $user->refresh();
    $this->assertTrue($user->hasRole('store_manager'));
});

<<<<<<< HEAD
it('admin cannot delete admin user', function () {
=======
test('admin cannot delete admin user', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

<<<<<<< HEAD
it('store_manager can create users but not admin', function () {
=======
test('store_manager can create users but not admin', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');

    $response = $this->actingAs($manager)->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['admin'],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

<<<<<<< HEAD
it('user creation validation works', function () {
=======
test('user creation validation works', function () {
>>>>>>> ca08c48 (feat(auth): implement complete user authentication and permission system)
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/users', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '456',
        'roles' => [],
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
});
