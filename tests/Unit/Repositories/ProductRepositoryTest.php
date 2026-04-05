<?php

use App\Models\Product;
use App\Models\Category;
use App\Repositories\Eloquent\ProductRepository;
use App\DTOs\Product\ProductFiltersDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new ProductRepository(new Product());
});

test('can create product', function () {
    $category = Category::factory()->create();
    
    $dto = new \App\DTOs\Product\CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        costPrice: 50.00,
        categoryId: $category->id,
        isActive: true,
        isTaxable: true,
        unit: 'pcs',
        weight: 1.5,
        volume: 2.0,
        brand: 'Test Brand',
        manufacturer: 'Test Manufacturer',
        supplier: 'Test Supplier',
        reorderPoint: 10,
        maxStock: 100,
        notes: 'Test notes'
    );

    $product = $this->repository->create($dto);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Test Product');
    expect($product->product_code)->toBe('TEST001');
    expect($product->price)->toEqual(99.99);
    expect($product->cost_price)->toEqual(50.00);
    expect($product->weight)->toEqual(1.5);
    expect($product->volume)->toEqual(2.0);
    expect($product->category_id)->toBe($category->id);
    expect($product->is_active)->toBeTrue();
    expect($product->is_taxable)->toBeTrue();
    expect($product->unit)->toBe('pcs');
    expect($product->brand)->toBe('Test Brand');
    expect($product->manufacturer)->toBe('Test Manufacturer');
    expect($product->supplier)->toBe('Test Supplier');
    expect($product->reorder_point)->toBe(10);
    expect($product->max_stock)->toBe(100);
    expect($product->notes)->toBe('Test notes');
});

test('can update product', function () {
    $product = Product::factory()->create();

    $dto = new \App\DTOs\Product\UpdateProductDTO(
        name: 'Updated Product',
        productCode: 'UPD001',
        price: 199.99,
        barcode: null,
        description: null,
        costPrice: 100.00,
        categoryId: null,
        isActive: null,
        isTaxable: null,
        unit: null,
        weight: null,
        volume: null,
        brand: null,
        manufacturer: null,
        supplier: null,
        reorderPoint: null,
        maxStock: null,
        notes: null,
        productId: $product->id
    );

    $updated = $this->repository->update($product->id, $dto);

    expect($updated->name)->toBe('Updated Product');
    expect($updated->product_code)->toBe('UPD001');
    expect($updated->price)->toEqual(199.99);
    expect($updated->cost_price)->toEqual(100.00);
    // Other fields remain unchanged since we didn't pass them in DTO
});

test('can find product by id', function () {
    $product = Product::factory()->create();

    $found = $this->repository->findById($product->id);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

test('can find product by barcode', function () {
    $product = Product::factory()->create(['barcode' => '1234567890128']);

    $found = $this->repository->findByBarcode('1234567890128');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

test('can find product by product code', function () {
    $product = Product::factory()->create(['product_code' => 'TEST001']);

    $found = $this->repository->findByProductCode('TEST001');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

test('can delete product', function () {
    $product = Product::factory()->create();

    $result = $this->repository->delete($product->id);

    expect($result)->toBeTrue();
    expect(Product::find($product->id))->toBeNull();
});

// Note: Test for 'cannot delete product with sales' removed because SaleItem model doesn't exist
// This test can be re-added when the sales module is implemented

test('product_code_exists returns true for existing code', function () {
    Product::factory()->create(['product_code' => 'TEST001']);

    $exists = $this->repository->productCodeExists('TEST001');

    expect($exists)->toBeTrue();
});

test('product_code_exists returns false for non-existing code', function () {
    $exists = $this->repository->productCodeExists('NONEXISTING');

    expect($exists)->toBeFalse();
});

test('product_code_exists excludes given id', function () {
    $product = Product::factory()->create(['product_code' => 'TEST001']);

    $exists = $this->repository->productCodeExists('TEST001', $product->id);

    expect($exists)->toBeFalse();
});

test('barcode_exists returns true for existing barcode', function () {
    Product::factory()->create(['barcode' => '1234567890128']);

    $exists = $this->repository->barcodeExists('1234567890128');

    expect($exists)->toBeTrue();
});

test('barcode_exists returns false for non-existing barcode', function () {
    $exists = $this->repository->barcodeExists('1234567890123');

    expect($exists)->toBeFalse();
});

test('barcode_exists excludes given id', function () {
    $product = Product::factory()->create(['barcode' => '1234567890128']);

    $exists = $this->repository->barcodeExists('1234567890128', $product->id);

    expect($exists)->toBeFalse();
});

test('can get paginated products with filters', function () {
    Product::factory()->count(25)->create();

    $filters = new ProductFiltersDTO();
    $paginated = $this->repository->paginateWithFilters($filters, 10);

    expect($paginated)->toHaveCount(10);
    expect($paginated->total())->toBe(25);
});

test('can get active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);

    $products = $this->repository->getActive();

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->is_active)->toBeTrue();
    });
});

test('can search products', function () {
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);
    Product::factory()->create(['name' => 'Apple iPad']);

    $results = $this->repository->search('Apple');

    expect($results)->toHaveCount(2);
    $results->each(function ($product) {
        expect($product->name)->toContain('Apple');
    });
});

test('can get products by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    Product::factory()->count(3)->withCategory($category1)->create();
    Product::factory()->count(2)->withCategory($category2)->create();

    $products = $this->repository->getByCategory($category1->id);

    expect($products)->toHaveCount(3);
    $products->each(function ($product) use ($category1) {
        expect($product->category_id)->toBe($category1->id);
    });
});

test('can get products by brand', function () {
    Product::factory()->count(3)->create(['brand' => 'Apple']);
    Product::factory()->count(2)->create(['brand' => 'Samsung']);

    $products = $this->repository->getByBrand('Apple');

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->brand)->toBe('Apple');
    });
});

test('can get products by supplier', function () {
    Product::factory()->count(3)->create(['supplier' => 'Supplier A']);
    Product::factory()->count(2)->create(['supplier' => 'Supplier B']);

    $products = $this->repository->getBySupplier('Supplier A');

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->supplier)->toBe('Supplier A');
    });
});

test('can get products by price range', function () {
    Product::factory()->create(['price' => 10.00]);
    Product::factory()->create(['price' => 50.00]);
    Product::factory()->create(['price' => 100.00]);
    Product::factory()->create(['price' => 200.00]);

    $products = $this->repository->getByPriceRange(25.00, 150.00);

    expect($products)->toHaveCount(2);
    $products->each(function ($product) {
        expect($product->price)->toBeGreaterThanOrEqual(25.00);
        expect($product->price)->toBeLessThanOrEqual(150.00);
    });
});

test('can get products with barcodes', function () {
    // Use unique valid EAN-13 barcodes to avoid conflicts with factory random generation
    Product::factory()->create(['barcode' => '1234567890128']);
    Product::factory()->create(['barcode' => '5901234123457']);
    Product::factory()->create(['barcode' => '4006381333931']);
    Product::factory()->count(2)->create(['barcode' => null]);

    $products = $this->repository->getWithBarcodes();

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->barcode)->not->toBeNull();
    });
});

test('can get products without barcodes', function () {
    Product::factory()->count(3)->create(['barcode' => null]);
    // Use unique valid EAN-13 barcodes to avoid conflicts
    Product::factory()->create(['barcode' => '1234567890128']);
    Product::factory()->create(['barcode' => '5901234123457']);

    $products = $this->repository->getWithoutBarcodes();

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->barcode)->toBeNull();
    });
});

test('can get products for dropdown', function () {
    Product::factory()->count(3)->create();

    $products = $this->repository->getForDropdown();

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product)->toHaveKeys(['id', 'name', 'product_code', 'barcode', 'price']);
    });
});

test('can get products for dropdown filtered by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    Product::factory()->count(3)->withCategory($category1)->create();
    Product::factory()->count(2)->withCategory($category2)->create();

    $products = $this->repository->getForDropdown($category1->id);

    expect($products)->toHaveCount(3);
    // getForDropdown filters by category but only returns specific fields (not category_id)
});

test('can get products with profit margins', function () {
    Product::factory()->count(3)->create([
        'price' => 100.00,
        'cost_price' => 50.00
    ]);
    Product::factory()->count(2)->create(['cost_price' => null]);

    $products = $this->repository->getWithProfitMargins();

    expect($products)->toHaveCount(3);
    $products->each(function ($product) {
        expect($product->cost_price)->not->toBeNull();
        expect($product->cost_price)->toBeGreaterThan(0);
    });
});

test('can bulk update products', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $product3 = Product::factory()->create();

    $updates = [
        $product1->id => ['is_active' => false],
        $product2->id => ['is_active' => false],
        $product3->id => ['is_active' => true],
    ];

    $updated = $this->repository->bulkUpdate($updates);

    expect($updated)->toBe(3);
    
    $product1->refresh();
    $product2->refresh();
    $product3->refresh();
    
    expect($product1->is_active)->toBeFalse();
    expect($product2->is_active)->toBeFalse();
    expect($product3->is_active)->toBeTrue();
});

test('can generate unique product code', function () {
    $code1 = $this->repository->generateUniqueProductCode();
    $code2 = $this->repository->generateUniqueProductCode();

    expect($code1)->not->toBe($code2);
    expect($code1)->toMatch('/^PRD\d{12}$/');
    expect($code2)->toMatch('/^PRD\d{12}$/');
});

test('can get products by multiple criteria', function () {
    Product::factory()->create(['brand' => 'Apple', 'is_active' => true]);
    Product::factory()->create(['brand' => 'Apple', 'is_active' => false]);
    Product::factory()->create(['brand' => 'Samsung', 'is_active' => true]);

    $products = $this->repository->getByMultipleCriteria([
        'brand' => 'Apple',
        'is_active' => true
    ]);

    expect($products)->toHaveCount(1);
    expect($products->first()->brand)->toBe('Apple');
    expect($products->first()->is_active)->toBeTrue();
});

test('can update product status', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $result = $this->repository->updateStatus($product->id, false);

    expect($result)->toBeTrue();
    
    $product->refresh();
    expect($product->is_active)->toBeFalse();
});

test('can count by status', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);

    $activeCount = $this->repository->countByStatus(true);
    $inactiveCount = $this->repository->countByStatus(false);

    expect($activeCount)->toBe(3);
    expect($inactiveCount)->toBe(2);
});

test('can count by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    Product::factory()->count(3)->withCategory($category1)->create();
    Product::factory()->count(2)->withCategory($category2)->create();
    Product::factory()->count(1)->create(['category_id' => null]);

    $counts = $this->repository->countByCategory();

    expect($counts)->toHaveCount(2);
    expect($counts->where('category_name', $category1->name)->first()->product_count)->toBe(3);
    expect($counts->where('category_name', $category2->name)->first()->product_count)->toBe(2);
});

test('can get products with stock', function () {
    Product::factory()->count(3)->create();

    $products = $this->repository->getWithStock();

    expect($products)->toHaveCount(3);
});

test('can get products by ids', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $product3 = Product::factory()->create();

    $products = $this->repository->getByIds([$product1->id, $product3->id]);

    expect($products)->toHaveCount(2);
    expect($products->pluck('id'))->toContain($product1->id, $product3->id);
    expect($products->pluck('id'))->not->toContain($product2->id);
});
