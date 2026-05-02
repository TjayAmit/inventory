<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->storeManager = User::factory()->create()->assignRole('store_manager');
    $this->warehouseStaff = User::factory()->create()->assignRole('warehouse_staff');
    $this->user = User::factory()->create()->assignRole('user');
});

test('can view products index', function () {
    Product::factory()->count(5)->create();

    $response = $this
        ->actingAs($this->user)
        ->get('/products');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        expect($page->component())->toBe('Products/Index');
        expect($page->props('products'))->toHaveCount(5);
    });
});

test('can view products index with filters', function () {
    Product::factory()->count(3)->create(['name' => 'Apple iPhone']);
    Product::factory()->count(2)->create(['name' => 'Samsung Galaxy']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products?search=Apple');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        expect($page->component())->toBe('Products/Index');
        expect($page->props('products'))->toHaveCount(3);
    });
});

test('can create product', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->get('/products/create');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        expect($page->component())->toBe('Products/Create');
        expect($page->props('categories'))->not->toBeNull();
    });
});

test('cannot create product without permission', function () {
    $response = $this
        ->actingAs($this->user)
        ->get('/products/create');

    $response->assertStatus(403);
});

test('can store product', function () {
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
        ->post('/products', $productData);

    $response->assertRedirect('/products');
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'barcode' => '1234567890128',
        'price' => 99.99,
        'cost_price' => 50.00,
        'category_id' => $category->id,
    ]);
});

test('cannot store product without permission', function () {
    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
    ];

    $response = $this
        ->actingAs($this->user)
        ->post('/products', $productData);

    $response->assertStatus(403);
});

test('can show product', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get("/products/{$product->id}");

    $response->assertStatus(200);
    $response->assertInertia(function ($page) use ($product) {
        expect($page->component())->toBe('Products/Show');
        expect($page->props('product')->id)->toBe($product->id);
    });
});

test('can edit product', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->storeManager)
        ->get("/products/{$product->id}/edit");

    $response->assertStatus(200);
    $response->assertInertia(function ($page) use ($product) {
        expect($page->component())->toBe('Products/Edit');
        expect($page->props('product')->id)->toBe($product->id);
        expect($page->props('categories'))->not->toBeNull();
    });
});

test('cannot edit product without permission', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get("/products/{$product->id}/edit");

    $response->assertStatus(403);
});

test('can update product', function () {
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
        ->put("/products/{$product->id}", $updateData);

    $response->assertRedirect('/products');
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

test('cannot update product without permission', function () {
    $product = Product::factory()->create();

    $updateData = [
        'name' => 'Updated Product',
        'product_code' => 'UPD001',
        'price' => 199.99,
    ];

    $response = $this
        ->actingAs($this->user)
        ->put("/products/{$product->id}", $updateData);

    $response->assertStatus(403);
});

test('can delete product', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->delete("/products/{$product->id}");

    $response->assertRedirect('/products');
    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('cannot delete product without permission', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->storeManager)
        ->delete("/products/{$product->id}");

    $response->assertStatus(403);
});

test('can toggle product status', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->storeManager)
        ->put("/products/{$product->id}/toggle-status");

    $response->assertRedirect();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => false,
    ]);
});

test('cannot toggle product status without permission', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->user)
        ->put("/products/{$product->id}/toggle-status");

    $response->assertStatus(403);
});

test('can generate barcode for product', function () {
    $product = Product::factory()->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->storeManager)
        ->put("/products/{$product->id}/generate-barcode");

    $response->assertRedirect();
    $product->refresh();
    expect($product->barcode)->not->toBeNull();
    expect($product->barcode)->toHaveLength(13);
});

test('cannot generate barcode without permission', function () {
    $product = Product::factory()->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->user)
        ->put("/products/{$product->id}/generate-barcode");

    $response->assertStatus(403);
});

test('can search products', function () {
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/search?term=Apple');

    $response->assertStatus(200);
    $response->assertJsonCount(1);
});

test('can find product by barcode', function () {
    $product = Product::factory()->create(['barcode' => '1234567890128']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/find-by-barcode?barcode=1234567890128');

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'id' => $product->id,
        'barcode' => '1234567890128',
    ]);
});

test('can find product by product code', function () {
    $product = Product::factory()->create(['product_code' => 'TEST001']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/find-by-product-code?product_code=TEST001');

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'id' => $product->id,
        'product_code' => 'TEST001',
    ]);
});

test('can get products dropdown', function () {
    Product::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get('/products/dropdown');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products by category', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->withCategory($category)->create();
    Product::factory()->count(2)->create();

    $response = $this
        ->actingAs($this->user)
        ->get("/products/by-category/{$category->id}");

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products by brand', function () {
    Product::factory()->count(3)->create(['brand' => 'Apple']);
    Product::factory()->count(2)->create(['brand' => 'Samsung']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/by-brand/Apple');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products by supplier', function () {
    Product::factory()->count(3)->create(['supplier' => 'Supplier A']);
    Product::factory()->count(2)->create(['supplier' => 'Supplier B']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/by-supplier/Supplier A');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products with barcodes', function () {
    Product::factory()->count(3)->create(['barcode' => '1234567890128']);
    Product::factory()->count(2)->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/with-barcodes');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products without barcodes', function () {
    Product::factory()->count(3)->create(['barcode' => null]);
    Product::factory()->count(2)->create(['barcode' => '1234567890128']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/without-barcodes');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get products with profit margins', function () {
    Product::factory()->count(3)->create([
        'price' => 100.00,
        'cost_price' => 50.00,
    ]);
    Product::factory()->count(2)->create(['cost_price' => null]);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/with-profit-margins');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('can get product statistics', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);
    Product::factory()->count(3)->create(['barcode' => '1234567890128']);
    Product::factory()->count(2)->create(['barcode' => null]);

    $response = $this
        ->actingAs($this->admin)
        ->get('/products/statistics');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'total',
        'active',
        'inactive',
        'with_barcodes',
        'without_barcodes',
        'active_percentage',
        'barcode_coverage',
    ]);
});

test('cannot get statistics without permission', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->get('/products/statistics');

    $response->assertStatus(403);
});

test('unauthenticated user cannot access product routes', function () {
    $product = Product::factory()->create();

    $this->get('/products')->assertRedirect('/login');
    $this->get('/products/create')->assertRedirect('/login');
    $this->post('/products')->assertRedirect('/login');
    $this->get("/products/{$product->id}")->assertRedirect('/login');
    $this->get("/products/{$product->id}/edit")->assertRedirect('/login');
    $this->put("/products/{$product->id}")->assertRedirect('/login');
    $this->delete("/products/{$product->id}")->assertRedirect('/login');
});

test('product validation works correctly', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => '', // Required field
            'price' => '', // Required field
        ]);

    $response->assertSessionHasErrors(['name', 'price']);
    $this->assertDatabaseMissing('products', ['name' => '']);
});

test('barcode validation works correctly', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Test Product',
            'product_code' => 'TEST001',
            'price' => 99.99,
            'barcode' => '1234567890123', // Invalid checksum
        ]);

    $response->assertSessionHasErrors('barcode');
    $this->assertDatabaseMissing('products', ['barcode' => '1234567890123']);
});

test('can create product without barcode', function () {
    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
        'barcode' => null, // No barcode
    ];

    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', $productData);

    $response->assertRedirect('/products');
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'barcode' => null,
    ]);
});

test('can create product without category', function () {
    $productData = [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
        'category_id' => null, // No category
    ];

    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', $productData);

    $response->assertRedirect('/products');
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'category_id' => null,
    ]);
});

test('warehouse staff can create products', function () {
    $response = $this
        ->actingAs($this->warehouseStaff)
        ->get('/products/create');

    $response->assertStatus(200);
});

test('warehouse staff can update products', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->get("/products/{$product->id}/edit");

    $response->assertStatus(200);
});

test('warehouse staff can toggle product status', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->put("/products/{$product->id}/toggle-status");

    $response->assertRedirect();
});

test('cannot create product with duplicate product code', function () {
    $existing = Product::factory()->create(['product_code' => 'UNIQUE001']);

    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Duplicate Code Product',
            'product_code' => 'UNIQUE001',
            'price' => 99.99,
        ]);

    $response->assertSessionHasErrors('product_code');
});

test('cannot create product with duplicate barcode', function () {
    $existing = Product::factory()->create(['barcode' => '1234567890128']);

    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Duplicate Barcode Product',
            'product_code' => 'TEST002',
            'barcode' => '1234567890128',
            'price' => 99.99,
        ]);

    $response->assertSessionHasErrors('barcode');
});

test('cannot create product with negative price', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Negative Price Product',
            'product_code' => 'TEST003',
            'price' => -10.00,
        ]);

    $response->assertSessionHasErrors('price');
});

test('cannot create product with negative cost price', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Negative Cost Product',
            'product_code' => 'TEST004',
            'price' => 99.99,
            'cost_price' => -5.00,
        ]);

    $response->assertSessionHasErrors('cost_price');
});

test('cannot create product with cost price higher than price', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->post('/products', [
            'name' => 'Loss Product',
            'product_code' => 'TEST005',
            'price' => 50.00,
            'cost_price' => 100.00,
        ]);

    $response->assertSessionHasErrors('cost_price');
});

test('cannot update product to have duplicate product code', function () {
    $product1 = Product::factory()->create(['product_code' => 'PROD001']);
    $product2 = Product::factory()->create(['product_code' => 'PROD002']);

    $response = $this
        ->actingAs($this->storeManager)
        ->put("/products/{$product2->id}", [
            'name' => $product2->name,
            'product_code' => 'PROD001',
            'price' => $product2->price,
        ]);

    $response->assertSessionHasErrors('product_code');
});

test('returns 404 for non-existent product show', function () {
    $response = $this
        ->actingAs($this->user)
        ->get('/products/99999');

    $response->assertNotFound();
});

test('returns 404 for non-existent product edit', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->get('/products/99999/edit');

    $response->assertNotFound();
});

test('cannot update non-existent product', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->put('/products/99999', [
            'name' => 'Updated Name',
            'product_code' => 'UPD001',
            'price' => 99.99,
        ]);

    $response->assertNotFound();
});

test('cannot delete non-existent product', function () {
    $response = $this
        ->actingAs($this->admin)
        ->delete('/products/99999');

    $response->assertNotFound();
});

test('cannot toggle status of non-existent product', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->put('/products/99999/toggle-status');

    $response->assertNotFound();
});

test('cannot generate barcode for non-existent product', function () {
    $response = $this
        ->actingAs($this->storeManager)
        ->put('/products/99999/generate-barcode');

    $response->assertNotFound();
});

test('search returns empty results for non-matching term', function () {
    Product::factory()->create(['name' => 'iPhone']);
    Product::factory()->create(['name' => 'Samsung']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/search?term=NonExistentProduct');

    $response->assertStatus(200);
    $response->assertJsonCount(0);
});

test('find by barcode returns 404 for non-existent barcode', function () {
    $response = $this
        ->actingAs($this->user)
        ->get('/products/find-by-barcode?barcode=9999999999999');

    $response->assertNotFound();
});

test('find by product code returns 404 for non-existent code', function () {
    $response = $this
        ->actingAs($this->user)
        ->get('/products/find-by-product-code?product_code=NONEXISTENT');

    $response->assertNotFound();
});

test('products by brand returns empty for non-existent brand', function () {
    Product::factory()->count(3)->create(['brand' => 'Apple']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/by-brand/NonExistentBrand');

    $response->assertStatus(200);
    $response->assertJsonCount(0);
});

test('products by supplier returns empty for non-existent supplier', function () {
    Product::factory()->count(3)->create(['supplier' => 'Supplier A']);

    $response = $this
        ->actingAs($this->user)
        ->get('/products/by-supplier/NonExistentSupplier');

    $response->assertStatus(200);
    $response->assertJsonCount(0);
});

test('statistics return zero when no products exist', function () {
    $response = $this
        ->actingAs($this->admin)
        ->get('/products/statistics');

    $response->assertStatus(200);
    $response->assertJson([
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'with_barcodes' => 0,
        'without_barcodes' => 0,
        'active_percentage' => 0,
        'barcode_coverage' => 0,
    ]);
});

test('warehouse staff cannot delete products', function () {
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($this->warehouseStaff)
        ->delete("/products/{$product->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('regular user cannot create products', function () {
    $response = $this
        ->actingAs($this->user)
        ->get('/products/create');

    $response->assertForbidden();
});
