<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Laravel\Sanctum\Sanctum; // Sanctum not installed, using regular auth

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->storeManager = User::factory()->create()->assignRole('store_manager');
    $this->warehouseStaff = User::factory()->create()->assignRole('warehouse_staff');
    $this->user = User::factory()->create()->assignRole('user');
});

test('can list categories via API', function () {
    Category::factory()->count(5)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(5);
});

test('can create category via API', function () {
    $categoryData = [
        'name' => 'Test Category',
        'description' => 'Test description',
        'parent_id' => null,
        'is_active' => true,
        'sort_order' => 1,
    ];

    $response = $this
        ->actingAs($this->admin)
        ->postJson('/api/v1/categories', $categoryData);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('categories', [
        'name' => 'Test Category',
        'description' => 'Test description',
    ]);
});

test('cannot create category via API without permission', function () {
    $categoryData = [
        'name' => 'Test Category',
        'description' => 'Test description',
    ];

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->postJson('/api/v1/categories', $categoryData);

    $response->assertStatus(403);
});

test('can show category via API', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $category->id,
        ],
    ]);
});

test('can update category via API', function () {
    $category = Category::factory()->create();

    $updateData = [
        'name' => 'Updated Category',
        'description' => 'Updated description',
        'is_active' => false,
        'sort_order' => 5,
    ];

    $response = $this
        ->actingAs($this->admin)
        ->putJson("/api/v1/categories/{$category->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category',
        'description' => 'Updated description',
        'is_active' => false,
        'sort_order' => 5,
    ]);
});

test('cannot update category via API without permission', function () {
    $category = Category::factory()->create();

    $updateData = [
        'name' => 'Updated Category',
        'description' => 'Updated description',
    ];

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/categories/{$category->id}", $updateData);

    $response->assertStatus(403);
});

test('can delete category via API', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('cannot delete category via API without permission', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->storeManager)
        ->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(403);
});

test('can get active categories via API', function () {
    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/active');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get category tree via API', function () {
    $root = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $root->id]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/tree');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(1); // Only root categories
});

test('can get root categories via API', function () {
    Category::factory()->count(2)->create(); // Root categories
    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]); // Child category

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/root');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
});

test('can get child categories via API', function () {
    $parent = Category::factory()->create();
    Category::factory()->count(3)->create(['parent_id' => $parent->id]);
    Category::factory()->create(); // Root category

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/categories/{$parent->id}/children");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can search categories via API', function () {
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Books']);
    Category::factory()->create(['name' => 'Electronic Gadgets']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/search?term=Electronic&limit=10');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
});

test('can get categories dropdown via API', function () {
    Category::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/dropdown');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get categories dropdown excluding specific ID via API', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $category3 = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/categories/dropdown?exclude_id={$category1->id}");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
    $categoryIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($categoryIds)->not->toContain($category1->id);
    expect($categoryIds)->toContain($category2->id);
    expect($categoryIds)->toContain($category3->id);
});

test('can toggle category status via API', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->admin)
        ->putJson("/api/v1/categories/{$category->id}/toggle-status");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

test('cannot toggle category status via API without permission', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/categories/{$category->id}/toggle-status");

    $response->assertStatus(403);
});

test('can update sort order via API', function () {
    $category1 = Category::factory()->create(['sort_order' => 5]);
    $category2 = Category::factory()->create(['sort_order' => 3]);
    $category3 = Category::factory()->create(['sort_order' => 1]);

    $response = $this
        ->actingAs($this->admin)
        ->putJson('/api/v1/categories/sort-order', [
            'category_ids' => [$category3->id, $category2->id, $category1->id],
        ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    
    $category1->refresh();
    $category2->refresh();
    $category3->refresh();
    
    expect($category3->sort_order)->toBe(0);
    expect($category2->sort_order)->toBe(1);
    expect($category1->sort_order)->toBe(2);
});

test('cannot update sort order via API without permission', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson('/api/v1/categories/sort-order', [
            'category_ids' => [1, 2, 3],
        ]);

    $response->assertStatus(403);
});

test('can move category via API', function () {
    $category = Category::factory()->create();
    $newParent = Category::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->putJson("/api/v1/categories/{$category->id}/move", [
            'parent_id' => $newParent->id,
        ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'parent_id' => $newParent->id,
    ]);
});

test('cannot move category via API without permission', function () {
    $category = Category::factory()->create();
    $newParent = Category::factory()->create();

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/categories/{$category->id}/move", [
            'parent_id' => $newParent->id,
        ]);

    $response->assertStatus(403);
});

test('can get category statistics via API', function () {
    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);

    $response = $this
        ->actingAs($this->admin)
        ->getJson('/api/v1/categories/statistics');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'total',
            'active',
            'inactive',
            'root',
            'active_percentage',
        ],
        'message',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('data.total'))->toBe(5);
    expect($response->json('data.active'))->toBe(3);
    expect($response->json('data.inactive'))->toBe(2);
});

test('cannot get statistics via API without permission', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->getJson('/api/v1/categories/statistics');

    $response->assertStatus(403);
});

test('can get categories with product counts via API', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    Product::factory()->count(3)->withCategory($category1)->create();
    Product::factory()->count(2)->withCategory($category2)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories/with-product-counts');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
    
    $categoryData = collect($response->json('data'))->keyBy('id');
    expect($categoryData[$category1->id]['products_count'])->toBe(3);
    expect($categoryData[$category2->id]['products_count'])->toBe(2);
});

test('can get category descendants via API', function () {
    $root = Category::factory()->create();
    $child1 = Category::factory()->create(['parent_id' => $root->id]);
    $child2 = Category::factory()->create(['parent_id' => $root->id]);
    $grandchild = Category::factory()->create(['parent_id' => $child1->id]);

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/categories/{$root->id}/descendants");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('unauthenticated user cannot access category API', function () {
    $category = Category::factory()->create();

    $this->getJson('/api/v1/categories')->assertStatus(401);
    $this->postJson('/api/v1/categories')->assertStatus(401);
    $this->getJson("/api/v1/categories/{$category->id}")->assertStatus(401);
    $this->putJson("/api/v1/categories/{$category->id}")->assertStatus(401);
    $this->deleteJson("/api/v1/categories/{$category->id}")->assertStatus(401);
});

test('category validation works correctly via API', function () {
    $response = $this
        ->actingAs($this->admin)
        ->postJson('/api/v1/categories', [
            'name' => '', // Required field
            'description' => '',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
    $this->assertDatabaseMissing('categories', ['name' => '']);
});

test('cannot create category with non-existent parent via API', function () {
    $response = $this
        ->actingAs($this->admin)
        ->postJson('/api/v1/categories', [
            'name' => 'Invalid Child Category',
            'parent_id' => 999, // Non-existent parent
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['parent_id']);
    $this->assertDatabaseMissing('categories', ['name' => 'Invalid Child Category']);
});

test('API responses follow consistent structure', function () {
    Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/categories');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    $response->assertJson(['message' => 'Categories retrieved successfully.']);
});

test('API error responses follow consistent structure', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->postJson('/api/v1/categories', [
            'name' => 'Test Category',
        ]);

    $response->assertStatus(403);
    $response->assertJsonStructure([
        'success',
        'message',
    ]);
    $response->assertJson(['success' => false]);
});
