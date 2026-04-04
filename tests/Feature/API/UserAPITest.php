<?php

use App\Models\User;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

it('API can list users', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    User::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
                'from',
                'to'
            ]
        ])
        ->assertJson(['success' => true]);
});

it('API requires authentication to list users', function () {
    $this->setUpRolesAndPermissions();
    
    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(401);
});

it('API can create user', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['cashier']
    ];

    $response = $this->postJson('/api/v1/users', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
        ])
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);
});

it('API prevents non-admin from creating admin user', function () {
    $this->setUpRolesAndPermissions();
    
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');
    
    $this->actingAs($manager);

    $userData = [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['admin']
    ];

    $response = $this->postJson('/api/v1/users', $userData);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthorized action'
        ]);
});

it('API validates user creation', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $response = $this->postJson('/api/v1/users', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '456',
        'roles' => []
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'roles']);
});

it('API can show user', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('cashier');

    $response = $this->getJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
        ])
        ->assertJson(['success' => true])
        ->assertJson(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]]);
});

it('API returns 404 for non-existent user', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $response = $this->getJson('/api/v1/users/999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'User not found'
        ]);
});

it('API can update user', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('cashier');

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'roles' => ['store_manager']
    ];

    $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
        ])
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com'
    ]);
});

it('API can update user password', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('cashier');

    $updateData = [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'roles' => ['cashier']
    ];

    $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

    $response->assertStatus(200);
});

it('API can delete user', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('cashier');

    $response = $this->deleteJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('API prevents deleting admin users', function () {
    $this->setUpRolesAndPermissions();
    
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');
    
    $this->actingAs($manager);

    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');

    $response = $this->deleteJson("/api/v1/users/{$adminUser->id}");

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthorized action'
        ]);
});

it('API can search users', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create(['name' => 'Admin User', 'email' => 'admin@example.com']);
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->getJson('/api/v1/users/search?query=John');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
            ],
            'count'
        ])
        ->assertJson(['success' => true]);

    expect($response->json('count'))->toBe(2);
});

it('API validates search parameters', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $response = $this->getJson('/api/v1/users/search?query=a');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['query']);
});

it('API can get user statistics', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    $response = $this->getJson('/api/v1/users/statistics');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_users',
                'admin_count',
                'store_manager_count',
                'cashier_count'
            ]
        ])
        ->assertJson(['success' => true]);
});

it('API applies rate limiting', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin);

    // Make multiple requests to test rate limiting
    $responses = collect(range(1, 65))->map(function () {
        return $this->getJson('/api/v1/users');
    });

    // Should hit rate limiting after 60 requests
    $lastResponse = $responses->last();
    $lastResponse->assertStatus(429);
});
