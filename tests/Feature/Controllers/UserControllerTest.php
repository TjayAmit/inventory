<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasRolesAndPermissions;

uses(RefreshDatabase::class);
uses(HasRolesAndPermissions::class);

beforeEach(function () {
    $this->setUpRolesAndPermissions();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->storeManager = User::factory()->create();
    $this->storeManager->assignRole('store_manager');

    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');

    $this->warehouseStaff = User::factory()->create();
    $this->warehouseStaff->assignRole('warehouse_staff');

    $this->regularUser = User::factory()->create();
    $this->regularUser->assignRole('user');
});

// Index Tests
describe('index', function () {
    it('admin can view users list', function () {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
    });

    it('store manager can view users list', function () {
        $response = $this->actingAs($this->storeManager)->get(route('users.index'));
        $response->assertOk();
    });

    it('cashier cannot view users list', function () {
        $response = $this->actingAs($this->cashier)->get(route('users.index'));
        $response->assertForbidden();
    });

    it('warehouse staff cannot view users list', function () {
        $response = $this->actingAs($this->warehouseStaff)->get(route('users.index'));
        $response->assertForbidden();
    });

    it('users list can be filtered by search term', function () {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)->get(route('users.index', ['search' => 'John']));

        $response->assertOk();
    });

    it('users list can be filtered by role', function () {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');

        $response = $this->actingAs($this->admin)->get(route('users.index', ['role' => 'cashier']));

        $response->assertOk();
    });

    it('users list supports pagination parameters', function () {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)->get(route('users.index', [
            'per_page' => 5,
            'sort_by' => 'name',
            'sort_direction' => 'asc'
        ]));

        $response->assertOk();
    });

    it('unauthenticated users cannot view users list', function () {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    });
});

// Create Tests
describe('create', function () {
    it('admin can view create user form', function () {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
    });

    it('store manager can view create user form', function () {
        $response = $this->actingAs($this->storeManager)->get(route('users.create'));
        $response->assertOk();
    });

    it('cashier cannot view create user form', function () {
        $response = $this->actingAs($this->cashier)->get(route('users.create'));
        $response->assertForbidden();
    });

    it('create form shows available roles based on user permissions', function () {
        $response = $this->actingAs($this->storeManager)->get(route('users.create'));

        $response->assertOk();
    });
});

// Store Tests
describe('store', function () {
    it('admin can create new user', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    });

    it('store manager can create new user', function () {
        $userData = [
            'name' => 'Manager Created User',
            'email' => 'managercreated@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->storeManager)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'managercreated@example.com']);
    });

    it('cashier cannot create new user', function () {
        $userData = [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->cashier)->post(route('users.store'), $userData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
    });

    it('cannot create user with duplicate email', function () {
        $existing = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('email');
    });

    it('cannot create user without required fields', function () {
        $response = $this->actingAs($this->admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
    });

    it('cannot create user with invalid email format', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email-format',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('email');
    });

    it('cannot create user with password less than 8 characters', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('password');
    });

    it('cannot create user with mismatched passwords', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Different456',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('password');
    });

    it('cannot create user without roles', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => [],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('roles');
    });

    it('cannot create user with invalid role', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['non-existent-role'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors('roles.0');
    });

    it('non-admin cannot create admin user', function () {
        $userData = [
            'name' => 'Fake Admin',
            'email' => 'fakeadmin@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['admin'],
        ];

        $response = $this->actingAs($this->storeManager)->post(route('users.store'), $userData);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'fakeadmin@example.com']);
    });

    it('admin can create admin user', function () {
        $userData = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'roles' => ['admin'],
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newadmin@example.com']);
    });
});

// Show Tests
describe('show', function () {
    it('admin can view user details', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->admin)->get(route('users.show', $user));

        $response->assertOk();
    });

    it('store manager can view user details', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->storeManager)->get(route('users.show', $user));

        $response->assertOk();
    });

    it('cashier cannot view user details', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->cashier)->get(route('users.show', $user));

        $response->assertForbidden();
    });

    it('returns 404 for non-existent user', function () {
        $response = $this->actingAs($this->admin)->get(route('users.show', 99999));

        $response->assertNotFound();
    });
});

// Edit Tests
describe('edit', function () {
    it('admin can view edit user form', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->admin)->get(route('users.edit', $user));

        $response->assertOk();
    });

    it('store manager can view edit user form', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->storeManager)->get(route('users.edit', $user));

        $response->assertOk();
    });

    it('cashier cannot view edit user form', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->cashier)->get(route('users.edit', $user));

        $response->assertForbidden();
    });
});

// Update Tests
describe('update', function () {
    it('admin can update user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'roles' => ['store_manager'],
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    });

    it('store manager can update user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $updateData = [
            'name' => 'Manager Updated',
            'email' => $user->email,
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->storeManager)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Manager Updated',
        ]);
    });

    it('cashier cannot update user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $updateData = [
            'name' => 'Unauthorized Update',
            'email' => $user->email,
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->cashier)->put(route('users.update', $user), $updateData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['name' => 'Unauthorized Update']);
    });

    it('can update user with new password', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
    });

    it('cannot update user with duplicate email', function () {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user1->assignRole('cashier');
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $user2->assignRole('cashier');

        $updateData = [
            'name' => $user1->name,
            'email' => 'user2@example.com',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $user1), $updateData);

        $response->assertSessionHasErrors('email');
    });

    it('cannot update user without required fields', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => '',
            'email' => '',
            'roles' => [],
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'roles']);
    });

    it('non-admin cannot update user to admin role', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['admin'],
        ];

        $response = $this->actingAs($this->storeManager)->put(route('users.update', $user), $updateData);

        $response->assertSessionHas('error');
    });

    it('non-admin cannot update admin user', function () {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $updateData = [
            'name' => 'Trying to Update Admin',
            'email' => $adminUser->email,
            'roles' => ['admin'],
        ];

        $response = $this->actingAs($this->storeManager)->put(route('users.update', $adminUser), $updateData);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['name' => 'Trying to Update Admin']);
    });

    it('cannot update non-existent user', function () {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', 99999), $updateData);

        $response->assertNotFound();
    });
});

// Destroy Tests
describe('destroy', function () {
    it('admin can delete user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('store manager cannot delete user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->storeManager)->delete(route('users.destroy', $user));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    });

    it('cashier cannot delete user', function () {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($this->cashier)->delete(route('users.destroy', $user));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    });

    it('admin cannot delete themselves', function () {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    });

    it('admin cannot delete other admin users', function () {
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');
        $otherAdminId = $otherAdmin->id;

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $otherAdmin));

        // Verify the other admin still exists in database
        $this->assertDatabaseHas('users', ['id' => $otherAdminId]);
    });

    it('returns error when deleting non-existent user', function () {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', 99999));

        $response->assertNotFound();
    });
});
