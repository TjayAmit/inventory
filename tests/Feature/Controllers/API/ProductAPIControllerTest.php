<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->storeManager = User::factory()->create()->assignRole('store_manager');
    $this->warehouseStaff = User::factory()->create()->assignRole('warehouse_staff');
    $this->user = User::factory()->create()->assignRole('user');
});

test('can list products via API', function () {
    Product::factory()->count(5)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'filters',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(5);
});

test('can list products with filters via API', function () {
    Product::factory()->count(3)->create(['name' => 'Apple iPhone']);
    Product::factory()->count(2)->create(['name' => 'Samsung Galaxy']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products?search=Apple');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can create product via API', function () {
    $category = Category::factory()->create();

    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'barcode' => '1234567890128',
        'description' => 'Test description',
        'price' => 99.99,
        'cost_price' => 50.00,
        'category_id' => $category->id,
        'is_active' => true,
        'is_taxable' => true,
        'unit' => 'pcs',
        'weight' => 1.5,
        'volume' => 2.0,
        'brand' => 'Test Brand',
        'manufacturer' => 'Test Manufacturer',
        'supplier' => 'Test Supplier',
        'reorder_point' => 10,
        'max_stock' => 100,
        'notes' => 'Test notes',
    ];

    $response = $this
        ->actingAs($this->storeManager)
        ->postJson('/api/v1/products', $productData);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'barcode' => '1234567890128',
        'price' => 99.99,
        'cost_price' => 50.00,
        'category_id' => $category->id,
    ]);
});

test('cannot create product via API without permission', function () {
    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
    ];

    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/v1/products', $productData);

    $response->assertStatus(403);
});

test('can show product via API', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $product->id,
        ],
    ]);
});

test('can update product via API', function () {
    $product = Product::factory()->create();

    $updateData = [
        'name' => 'Updated Product',
        'product_code' => 'UPD001',
        'barcode' => '9876543210987',
        'description' => 'Updated description',
        'price' => 199.99,
        'cost_price' => 100.00,
        'category_id' => null,
        'is_active' => false,
        'is_taxable' => false,
        'unit' => 'kg',
        'weight' => 2.5,
        'volume' => 3.0,
        'brand' => 'Updated Brand',
        'manufacturer' => 'Updated Manufacturer',
        'supplier' => 'Updated Supplier',
        'reorder_point' => 20,
        'max_stock' => 200,
        'notes' => 'Updated notes',
    ];

    $response = $this
        ->actingAs($this->storeManager)
        ->putJson("/api/v1/products/{$product->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
        'product_code' => 'UPD001',
        'barcode' => '9876543210987',
        'price' => 199.99,
        'cost_price' => 100.00,
        'is_active' => false,
        'is_taxable' => false,
    ]);
});

test('cannot update product via API without permission', function () {
    $product = Product::factory()->create();

    $updateData = [
        'name' => 'Updated Product',
        'product_code' => 'UPD001',
        'price' => 199.99,
    ];

    $response = $this
        ->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}", $updateData);

    $response->assertStatus(403);
});

test('can delete product via API', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('cannot delete product via API without permission', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->storeManager)
        ->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(403);
});

test('can get active products via API', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/active');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can search products via API', function () {
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);
    Product::factory()->create(['name' => 'Apple iPad']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/search?term=Apple&limit=10');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
});

test('can find product by barcode via API', function () {
    $product = Product::factory()->create(['barcode' => '1234567890128']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/find-by-barcode?barcode=1234567890128');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $product->id,
            'barcode' => '1234567890128',
        ],
    ]);
});

test('can find product by product code via API', function () {
    $product = Product::factory()->create(['product_code' => 'TEST001']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/find-by-product-code?product_code=TEST001');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $product->id,
            'product_code' => 'TEST001',
        ],
    ]);
});

test('can get products dropdown via API', function () {
    Product::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/dropdown');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products dropdown filtered by category via API', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->withCategory($category)->create();
    Product::factory()->count(2)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/products/dropdown?category_id={$category->id}");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products by category via API', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->withCategory($category)->create();
    Product::factory()->count(2)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/v1/products/by-category/{$category->id}");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products by brand via API', function () {
    Product::factory()->count(3)->create(['brand' => 'Apple']);
    Product::factory()->count(2)->create(['brand' => 'Samsung']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/by-brand/Apple');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products by supplier via API', function () {
    Product::factory()->count(3)->create(['supplier' => 'Supplier A']);
    Product::factory()->count(2)->create(['supplier' => 'Supplier B']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/by-supplier/Supplier A');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products with barcodes via API', function () {
    Product::factory()->count(3)->create(['barcode' => '1234567890128']);
    Product::factory()->count(2)->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/with-barcodes');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products without barcodes via API', function () {
    Product::factory()->count(3)->create(['barcode' => null]);
    Product::factory()->count(2)->create(['barcode' => '1234567890128']);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/without-barcodes');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can get products with profit margins via API', function () {
    Product::factory()->count(3)->create([
        'price' => 100.00,
        'cost_price' => 50.00,
    ]);
    Product::factory()->count(2)->create(['cost_price' => null]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/with-profit-margins');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('can toggle product status via API', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->storeManager)
        ->putJson("/api/v1/products/{$product->id}/toggle-status");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => false,
    ]);
});

test('cannot toggle product status via API without permission', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}/toggle-status");

    $response->assertStatus(403);
});

test('can generate barcode for product via API', function () {
    $product = Product::factory()->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->storeManager)
        ->putJson("/api/v1/products/{$product->id}/generate-barcode");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $product->refresh();
    expect($product->barcode)->not->toBeNull();
    expect($product->barcode)->toHaveLength(13);
});

test('cannot generate barcode via API without permission', function () {
    $product = Product::factory()->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}/generate-barcode");

    $response->assertStatus(403);
});

test('can get product statistics via API', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);
    Product::factory()->count(3)->create(['barcode' => '1234567890128']);
    Product::factory()->count(2)->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->admin)
        ->getJson('/api/v1/products/statistics');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'total',
            'active',
            'inactive',
            'with_barcodes',
            'without_barcodes',
            'active_percentage',
            'barcode_coverage',
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
        ->getJson('/api/v1/products/statistics');

    $response->assertStatus(403);
});

test('can bulk update products via API', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $product3 = Product::factory()->create();

    $updates = [
        $product1->id => ['is_active' => false],
        $product2->id => ['is_active' => false],
        $product3->id => ['is_active' => true],
    ];

    $response = $this
        ->actingAs($this->admin)
        ->putJson('/api/v1/products/bulk-update', $updates);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => ['updated_count'],
        'message',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('data.updated_count'))->toBe(3);
});

test('cannot bulk update products via API without permission', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson('/api/v1/products/bulk-update', [
            'updates' => [
                1 => ['is_active' => false],
            ],
        ]);

    $response->assertStatus(403);
});

test('can get products by price range via API', function () {
    Product::factory()->create(['price' => 10.00]);
    Product::factory()->create(['price' => 50.00]);
    Product::factory()->create(['price' => 100.00]);
    Product::factory()->create(['price' => 200.00]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products/by-price-range?min_price=25&max_price=150');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(2);
});

test('can import products via API', function () {
    $productsData = [
        [
            'name' => 'Imported Product 1',
            'price' => 99.99,
            'product_code' => 'IMP001',
        ],
        [
            'name' => 'Imported Product 2',
            'price' => 199.99,
            'product_code' => 'IMP002',
        ],
    ];

    $response = $this
        ->actingAs($this->admin)
        ->postJson('/api/v1/products/import', ['products' => $productsData]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('data.success'))->toBe(2);
    expect($response->json('data.failed'))->toBe(0);
});

test('cannot import products via API without permission', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->postJson('/api/v1/products/import', [
            'products' => [
                ['name' => 'Test Product', 'price' => 99.99],
            ],
        ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot access product API', function () {
    $product = Product::factory()->create();

    $this->getJson('/api/v1/products')->assertStatus(401);
    $this->postJson('/api/v1/products')->assertStatus(401);
    $this->getJson("/api/v1/products/{$product->id}")->assertStatus(401);
    $this->putJson("/api/v1/products/{$product->id}")->assertStatus(401);
    $this->deleteJson("/api/v1/products/{$product->id}")->assertStatus(401);
});

test('product validation works correctly via API', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->postJson('/api/v1/products', [
            'name' => '', // Required field
            'price' => '', // Required field
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'price']);
    $this->assertDatabaseMissing('products', ['name' => '']);
});

test('barcode validation works correctly via API', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'product_code' => 'TEST001',
            'price' => 99.99,
            'barcode' => '1234567890123', // Invalid checksum
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['barcode']);
    $this->assertDatabaseMissing('products', ['barcode' => '1234567890123']);
});

test('warehouse staff can access product API endpoints', function () {
    $product = Product::factory()->create();

    $this
        ->actingAs($this->warehouseStaff)
        ->getJson('/api/v1/products')
        ->assertStatus(200);

    $this
        ->actingAs($this->warehouseStaff)
        ->getJson("/api/v1/products/{$product->id}")
        ->assertStatus(200);

    $this
        ->actingAs($this->warehouseStaff)
        ->getJson('/api/v1/products/active')
        ->assertStatus(200);
});

test('warehouse staff can create products via API', function () {
    $category = Category::factory()->create();

    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
        'category_id' => $category->id,
    ];

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->postJson('/api/v1/products', $productData);

    $response->assertStatus(201);
    $response->assertJson(['success' => true]);
});

test('warehouse staff can update products via API', function () {
    $product = Product::factory()->create();

    $updateData = [
        'name' => 'Updated Product',
        'product_code' => 'UPD001',
        'price' => 199.99,
    ];

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/products/{$product->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('warehouse staff can toggle product status via API', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/products/{$product->id}/toggle-status");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('warehouse staff can generate barcode via API', function () {
    $product = Product::factory()->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->putJson("/api/v1/products/{$product->id}/generate-barcode");

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('API responses follow consistent structure', function () {
    Product::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/v1/products');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
        'filters',
        'message',
    ]);
    $response->assertJson(['success' => true]);
    $response->assertJson(['message' => 'Products retrieved successfully.']);
});

test('API error responses follow consistent structure', function () {
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/v1/products', [
            'name' => 'Test Product',
        ]);

    $response->assertStatus(403);
    $response->assertJsonStructure([
        'success',
        'message',
    ]);
    $response->assertJson(['success' => false]);
});
