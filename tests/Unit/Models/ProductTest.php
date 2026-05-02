<?php

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a product', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'product_code' => 'TEST001',
        'price' => 99.99,
        'cost_price' => 50.00,
        'is_active' => true,
        'is_taxable' => true,
    ]);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Test Product');
    expect($product->product_code)->toBe('TEST001');
    expect($product->price)->toBe(99.99);
    expect($product->cost_price)->toBe(50.00);
    expect($product->is_active)->toBeTrue();
    expect($product->is_taxable)->toBeTrue();
});

test('product belongs to category', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->withCategory($category)->create();

    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->category->id)->toBe($category->id);
});

test('product can be without category', function () {
    $product = Product::factory()->withoutCategory()->create();

    expect($product->category_id)->toBeNull();
    expect($product->category)->toBeNull();
});

test('active scope returns only active products', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);
    Product::factory()->create(['is_active' => true]);

    $activeProducts = Product::active()->get();

    expect($activeProducts)->toHaveCount(2);
    $activeProducts->each(function ($product) {
        expect($product->is_active)->toBeTrue();
    });
});

test('taxable scope returns only taxable products', function () {
    Product::factory()->create(['is_taxable' => true]);
    Product::factory()->create(['is_taxable' => false]);
    Product::factory()->create(['is_taxable' => true]);

    $taxableProducts = Product::taxable()->get();

    expect($taxableProducts)->toHaveCount(2);
    $taxableProducts->each(function ($product) {
        expect($product->is_taxable)->toBeTrue();
    });
});

test('search scope finds products by name', function () {
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);
    Product::factory()->create(['name' => 'Apple iPad']);

    $results = Product::search('Apple')->get();

    expect($results)->toHaveCount(2);
    $results->each(function ($product) {
        expect($product->name)->toContain('Apple');
    });
});

test('search scope finds products by description', function () {
    Product::factory()->create(['description' => 'A great smartphone']);
    Product::factory()->create(['description' => 'A cheap tablet']);
    Product::factory()->create(['description' => 'An amazing phone']);

    $results = Product::search('great')->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->description)->toContain('great');
});

test('by_category scope filters by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    Product::factory()->withCategory($category1)->create();
    Product::factory()->withCategory($category1)->create();
    Product::factory()->withCategory($category2)->create();

    $category1Products = Product::byCategory($category1->id)->get();

    expect($category1Products)->toHaveCount(2);
    $category1Products->each(function ($product) use ($category1) {
        expect($product->category_id)->toBe($category1->id);
    });
});

test('by_price_range scope filters by price', function () {
    Product::factory()->create(['price' => 10.00]);
    Product::factory()->create(['price' => 50.00]);
    Product::factory()->create(['price' => 100.00]);
    Product::factory()->create(['price' => 200.00]);

    $results = Product::byPriceRange(25.00, 150.00)->get();

    expect($results)->toHaveCount(2);
    $results->each(function ($product) {
        expect($product->price)->toBeGreaterThanOrEqual(25.00);
        expect($product->price)->toBeLessThanOrEqual(150.00);
    });
});

test('barcode validation accepts valid EAN-13', function () {
    $product = Product::factory()->create([
        'barcode' => '1234567890128' // Valid checksum
    ]);

    expect($product->barcode)->toBe('1234567890128');
});

test('barcode validation rejects invalid EAN-13', function () {
    expect(fn() => Product::factory()->create([
        'barcode' => '1234567890123' // Invalid checksum
    ]))->toThrow(\Exception::class);
});

test('can find product by barcode', function () {
    $product = Product::factory()->create(['barcode' => '1234567890128']);

    $found = Product::findByBarcode('1234567890128');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

test('can find product by product code', function () {
    $product = Product::factory()->create(['product_code' => 'TEST001']);

    $found = Product::findByProductCode('TEST001');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

test('formatted_price returns proper format', function () {
    $product = Product::factory()->create(['price' => 99.99]);

    expect($product->formatted_price)->toBe('₱99.99');
});

test('formatted_cost_price returns proper format', function () {
    $product = Product::factory()->create(['cost_price' => 50.00]);

    expect($product->formatted_cost_price)->toBe('₱50.00');
});

test('profit_margin calculation works', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'cost_price' => 50.00
    ]);

    expect($product->profit_margin)->toBe(100.0); // 100% profit
});

test('profit_margin returns null without cost price', function () {
    $product = Product::factory()->create(['cost_price' => null]);

    expect($product->profit_margin)->toBeNull();
});

test('generate_unique_product_code creates unique codes', function () {
    $code1 = Product::generateUniqueProductCode();
    $code2 = Product::generateUniqueProductCode();

    expect($code1)->not->toBe($code2);
    expect($code1)->toMatch('/^PRD\d{10}$/');
    expect($code2)->toMatch('/^PRD\d{10}$/');
});

test('generate_dummy_barcode creates valid EAN-13', function () {
    $barcode = Product::generateDummyBarcode();

    expect($barcode)->toHaveLength(13);
    expect($barcode)->toMatch('/^\d{13}$/');
    
    // Verify checksum
    expect(Product::isValidEAN13Checksum($barcode))->toBeTrue();
});

test('product with no stock record has zero current stock and is not in stock', function () {
    $product = Product::factory()->create();

    expect($product->current_stock)->toBe(0);
    expect($product->is_in_stock)->toBeFalse();
});

test('product name is unique', function () {
    Product::factory()->create(['name' => 'Test Product']);

    expect(fn() => Product::factory()->create(['name' => 'Test Product']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('product code is unique', function () {
    Product::factory()->create(['product_code' => 'TEST001']);

    expect(fn() => Product::factory()->create(['product_code' => 'TEST001']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('barcode is unique when provided', function () {
    Product::factory()->create(['barcode' => '1234567890128']);

    expect(fn() => Product::factory()->create(['barcode' => '1234567890128']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('can soft delete product if enabled', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect($product->fresh()->trashed())->toBeTrue();
});

test('can restore soft deleted product', function () {
    $product = Product::factory()->create();
    $product->delete();

    $product->restore();

    expect($product->fresh()->trashed())->toBeFalse();
});

test('casts work correctly', function () {
    $product = Product::factory()->create([
        'price' => '99.99',
        'cost_price' => '50.50',
        'weight' => '1.234',
        'volume' => '2.345',
        'is_active' => true,
        'is_taxable' => false,
        'reorder_point' => '10',
        'max_stock' => '100',
    ]);

    expect($product->price)->toBeFloat();
    expect($product->cost_price)->toBeFloat();
    expect($product->weight)->toBeFloat();
    expect($product->volume)->toBeFloat();
    expect($product->is_active)->toBeBool();
    expect($product->is_taxable)->toBeBool();
    expect($product->reorder_point)->toBeInt();
    expect($product->max_stock)->toBeInt();
});
