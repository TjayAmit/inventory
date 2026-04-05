<?php

use App\DTOs\Product\CreateProductDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('can create valid DTO', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        costPrice: 50.00,
        categoryId: 1,
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

    expect($dto->getName())->toBe('Test Product');
    expect($dto->getProductCode())->toBe('TEST001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getCostPrice())->toBe(50.00);
    expect($dto->getCategoryId())->toBe(1);
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getIsTaxable())->toBeTrue();
    expect($dto->getUnit())->toBe('pcs');
    expect($dto->getWeight())->toBe(1.5);
    expect($dto->getVolume())->toBe(2.0);
    expect($dto->getBrand())->toBe('Test Brand');
    expect($dto->getManufacturer())->toBe('Test Manufacturer');
    expect($dto->getSupplier())->toBe('Test Supplier');
    expect($dto->getReorderPoint())->toBe(10);
    expect($dto->getMaxStock())->toBe(100);
    expect($dto->getNotes())->toBe('Test notes');
});

test('can create DTO with minimal data', function () {
    $dto = new CreateProductDTO(
        name: 'Minimal Product',
        productCode: 'MIN001',
        price: 99.99
    );

    expect($dto->getName())->toBe('Minimal Product');
    expect($dto->getProductCode())->toBe('MIN001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getCostPrice())->toBeNull();
    expect($dto->getCategoryId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue(); // Default value
    expect($dto->getIsTaxable())->toBeTrue(); // Default value
    expect($dto->getUnit())->toBe('pcs'); // Default value
    expect($dto->getWeight())->toBeNull();
    expect($dto->getVolume())->toBeNull();
    expect($dto->getBrand())->toBeNull();
    expect($dto->getManufacturer())->toBeNull();
    expect($dto->getSupplier())->toBeNull();
    expect($dto->getReorderPoint())->toBe(10); // Default value
    expect($dto->getMaxStock())->toBe(1000); // Default value
    expect($dto->getNotes())->toBeNull();
});

test('can create DTO without barcode', function () {
    $dto = new CreateProductDTO(
        name: 'Product Without Barcode',
        productCode: 'NOBAR001',
        price: 99.99,
        barcode: null
    );

    expect($dto->getName())->toBe('Product Without Barcode');
    expect($dto->getProductCode())->toBe('NOBAR001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getBarcode())->toBeNull();
});

test('validation passes with valid data', function () {
    $dto = new CreateProductDTO(
        name: 'Valid Product',
        productCode: 'VALID001',
        price: 99.99,
        barcode: '1234567890128'
    );

    expect(fn() => $dto->validate())->not->toThrow(\Exception::class);
});

test('validation fails with empty name', function () {
    $dto = new CreateProductDTO(
        name: '',
        productCode: 'TEST001',
        price: 99.99
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with name too long', function () {
    $dto = new CreateProductDTO(
        name: str_repeat('a', 201), // 201 characters
        productCode: 'TEST001',
        price: 99.99
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with empty product code', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: '',
        price: 99.99
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with product code too long', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: str_repeat('a', 51), // 51 characters
        price: 99.99
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid price', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 0 // Less than minimum
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with price too high', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 1000000 // Too high
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid cost price', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        costPrice: -10 // Negative
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid barcode', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        barcode: '1234567890123' // Invalid checksum
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with barcode wrong length', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        barcode: '12345' // Too short
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid category', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        categoryId: 999 // Non-existent
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation rules are correct', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99
    );

    $rules = $dto->rules();

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('product_code');
    expect($rules)->toHaveKey('barcode');
    expect($rules)->toHaveKey('description');
    expect($rules)->toHaveKey('price');
    expect($rules)->toHaveKey('cost_price');
    expect($rules)->toHaveKey('category_id');
    expect($rules)->toHaveKey('is_active');
    expect($rules)->toHaveKey('is_taxable');
    expect($rules)->toHaveKey('unit');
    expect($rules)->toHaveKey('weight');
    expect($rules)->toHaveKey('volume');
    expect($rules)->toHaveKey('brand');
    expect($rules)->toHaveKey('manufacturer');
    expect($rules)->toHaveKey('supplier');
    expect($rules)->toHaveKey('reorder_point');
    expect($rules)->toHaveKey('max_stock');
    expect($rules)->toHaveKey('notes');

    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('max:200');
    expect($rules['product_code'])->toContain('required');
    expect($rules['product_code'])->toContain('max:50');
    expect($rules['barcode'])->toContain('nullable');
    expect($rules['barcode'])->toContain('size:13');
    expect($rules['barcode'])->toContain('regex:/^[0-9]+$/');
    expect($rules['price'])->toContain('required');
    expect($rules['price'])->toContain('min:0.01');
    expect($rules['price'])->toContain('max:999999.99');
    expect($rules['cost_price'])->toContain('nullable');
    expect($rules['cost_price'])->toContain('min:0.01');
    expect($rules['cost_price'])->toContain('max:999999.99');
    expect($rules['category_id'])->toContain('nullable');
    expect($rules['category_id'])->toContain('integer');
    expect($rules['category_id'])->toContain('exists:categories,id');
});

test('validation messages are correct', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99
    );

    $messages = $dto->messages();

    expect($messages)->toHaveKey('name.required');
    expect($messages)->toHaveKey('name.max');
    expect($messages)->toHaveKey('product_code.required');
    expect($messages)->toHaveKey('product_code.max');
    expect($messages)->toHaveKey('barcode.size');
    expect($messages)->toHaveKey('barcode.regex');
    expect($messages)->toHaveKey('price.required');
    expect($messages)->toHaveKey('price.min');
    expect($messages)->toHaveKey('price.max');
    expect($messages)->toHaveKey('cost_price.min');
    expect($messages)->toHaveKey('cost_price.max');
    expect($messages)->toHaveKey('category_id.exists');

    expect($messages['name.required'])->toBe('The product name is required.');
    expect($messages['name.max'])->toBe('The product name may not be greater than 200 characters.');
    expect($messages['product_code.required'])->toBe('The product code is required.');
    expect($messages['product_code.max'])->toBe('The product code may not be greater than 50 characters.');
    expect($messages['barcode.size'])->toBe('The barcode must be exactly 13 characters.');
    expect($messages['price.required'])->toBe('The price is required.');
    expect($messages['price.min'])->toBe('The price must be at least 0.01.');
    expect($messages['category_id.exists'])->toBe('The selected category does not exist.');
});

test('toArray returns correct data', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        costPrice: 50.00,
        categoryId: 1,
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

    $array = $dto->toArray();

    expect($array)->toHaveKey('name');
    expect($array)->toHaveKey('product_code');
    expect($array)->toHaveKey('price');
    expect($array)->toHaveKey('cost_price');
    expect($array)->toHaveKey('category_id');
    expect($array)->toHaveKey('is_active');
    expect($array)->toHaveKey('is_taxable');
    expect($array)->toHaveKey('unit');
    expect($array)->toHaveKey('weight');
    expect($array)->toHaveKey('volume');
    expect($array)->toHaveKey('brand');
    expect($array)->toHaveKey('manufacturer');
    expect($array)->toHaveKey('supplier');
    expect($array)->toHaveKey('reorder_point');
    expect($array)->toHaveKey('max_stock');
    expect($array)->toHaveKey('notes');

    expect($array['name'])->toBe('Test Product');
    expect($array['product_code'])->toBe('TEST001');
    expect($array['price'])->toBe(99.99);
    expect($array['cost_price'])->toBe(50.00);
    expect($array['category_id'])->toBe(1);
    expect($array['is_active'])->toBeTrue();
    expect($array['is_taxable'])->toBeTrue();
    expect($array['unit'])->toBe('pcs');
    expect($array['weight'])->toBe(1.5);
    expect($array['volume'])->toBe(2.0);
    expect($array['brand'])->toBe('Test Brand');
    expect($array['manufacturer'])->toBe('Test Manufacturer');
    expect($array['supplier'])->toBe('Test Supplier');
    expect($array['reorder_point'])->toBe(10);
    expect($array['max_stock'])->toBe(100);
    expect($array['notes'])->toBe('Test notes');
});

test('toJson returns correct JSON', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99
    );

    $json = $dto->toJson();

    expect($json)->toBeJson();
    expect($json)->toContain('Test Product');
    expect($json)->toContain('TEST001');
    expect($json)->toContain('99.99');
});

test('can create from array', function () {
    $data = [
        'name' => 'Array Product',
        'product_code' => 'ARRAY001',
        'price' => 199.99,
        'cost_price' => 100.00,
        'category_id' => 1,
        'is_active' => false,
        'is_taxable' => false,
        'unit' => 'kg',
        'weight' => 2.5,
        'volume' => 3.0,
        'brand' => 'Array Brand',
        'manufacturer' => 'Array Manufacturer',
        'supplier' => 'Array Supplier',
        'reorder_point' => 20,
        'max_stock' => 200,
        'notes' => 'Array notes',
    ];

    $dto = CreateProductDTO::fromArray($data);

    expect($dto->getName())->toBe('Array Product');
    expect($dto->getProductCode())->toBe('ARRAY001');
    expect($dto->getPrice())->toBe(199.99);
    expect($dto->getCostPrice())->toBe(100.00);
    expect($dto->getCategoryId())->toBe(1);
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getIsTaxable())->toBeFalse();
    expect($dto->getUnit())->toBe('kg');
    expect($dto->getWeight())->toBe(2.5);
    expect($dto->getVolume())->toBe(3.0);
    expect($dto->getBrand())->toBe('Array Brand');
    expect($dto->getManufacturer())->toBe('Array Manufacturer');
    expect($dto->getSupplier())->toBe('Array Supplier');
    expect($dto->getReorderPoint())->toBe(20);
    expect($dto->getMaxStock())->toBe(200);
    expect($dto->getNotes())->toBe('Array notes');
});

test('can create from JSON', function () {
    $json = json_encode([
        'name' => 'JSON Product',
        'product_code' => 'JSON001',
        'price' => 149.99,
        'cost_price' => 75.00,
        'category_id' => 1,
        'is_active' => true,
        'is_taxable' => true,
        'unit' => 'ltr',
        'weight' => 1.0,
        'volume' => 1.5,
        'brand' => 'JSON Brand',
        'manufacturer' => 'JSON Manufacturer',
        'supplier' => 'JSON Supplier',
        'reorder_point' => 15,
        'max_stock' => 150,
        'notes' => 'JSON notes',
    ]);

    $dto = CreateProductDTO::fromJson($json);

    expect($dto->getName())->toBe('JSON Product');
    expect($dto->getProductCode())->toBe('JSON001');
    expect($dto->getPrice())->toBe(149.99);
    expect($dto->getCostPrice())->toBe(75.00);
    expect($dto->getCategoryId())->toBe(1);
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getIsTaxable())->toBeTrue();
    expect($dto->getUnit())->toBe('ltr');
    expect($dto->getWeight())->toBe(1.0);
    expect($dto->getVolume())->toBe(1.5);
    expect($dto->getBrand())->toBe('JSON Brand');
    expect($dto->getManufacturer())->toBe('JSON Manufacturer');
    expect($dto->getSupplier())->toBe('JSON Supplier');
    expect($dto->getReorderPoint())->toBe(15);
    expect($dto->getMaxStock())->toBe(150);
    expect($dto->getNotes())->toBe('JSON notes');
});

test('helper methods work correctly', function () {
    $dto = new CreateProductDTO(
        name: 'Test Product',
        productCode: 'TEST001',
        price: 99.99,
        barcode: '1234567890128',
        categoryId: 1
    );

    expect($dto->getName())->toBe('Test Product');
    expect($dto->getProductCode())->toBe('TEST001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getBarcode())->toBe('1234567890128');
    expect($dto->getCategoryId())->toBe(1);
});

test('helper methods work for product without barcode', function () {
    $dto = new CreateProductDTO(
        name: 'No Barcode Product',
        productCode: 'NOBAR001',
        price: 99.99,
        barcode: null
    );

    expect($dto->getName())->toBe('No Barcode Product');
    expect($dto->getProductCode())->toBe('NOBAR001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getBarcode())->toBeNull();
});

test('helper methods work for product without category', function () {
    $dto = new CreateProductDTO(
        name: 'No Category Product',
        productCode: 'NOCAT001',
        price: 99.99,
        categoryId: null
    );

    expect($dto->getName())->toBe('No Category Product');
    expect($dto->getProductCode())->toBe('NOCAT001');
    expect($dto->getPrice())->toBe(99.99);
    expect($dto->getCategoryId())->toBeNull();
});

test('validation skips in unit tests', function () {
    // Define constant to simulate unit test environment
    if (!defined('PHPUNIT_RUNNING')) {
        define('PHPUNIT_RUNNING', true);
    }

    $dto = new CreateProductDTO(
        name: '', // Invalid name
        productCode: '', // Invalid product code
        price: 0, // Invalid price
        barcode: '123', // Invalid barcode
        categoryId: 999 // Invalid category
    );

    // Should not throw exception in unit test mode
    expect($dto->validate())->toBeNull(); // Validation should be skipped
});

test('can create product with valid EAN-13 barcode', function () {
    $dto = new CreateProductDTO(
        name: 'Product with Valid Barcode',
        productCode: 'BARCODE001',
        price: 99.99,
        barcode: '1234567890128' // Valid checksum
    );

    expect($dto->getBarcode())->toBe('1234567890128');
    expect(fn() => $dto->validate())->not->toThrow(\Exception::class);
});

test('can create product with zero cost price', function () {
    $dto = new CreateProductDTO(
        name: 'Free Product',
        productCode: 'FREE001',
        price: 99.99,
        costPrice: 0.00
    );

    expect($dto->getCostPrice())->toBe(0.00);
});

test('can create product with different units', function () {
    $units = ['pcs', 'kg', 'ltr', 'box', 'pack', 'meter', 'dozen'];
    
    foreach ($units as $unit) {
        $dto = new CreateProductDTO(
            name: "Product in {$unit}",
            productCode: strtoupper($unit) . '001',
            price: 99.99,
            unit: $unit
        );
        
        expect($dto->getUnit())->toBe($unit);
    }
});

test('can create product with maximum allowed values', function () {
    $dto = new CreateProductDTO(
        name: str_repeat('a', 200), // Max name length
        productCode: str_repeat('b', 50), // Max product code length
        price: 999999.99, // Max price
        costPrice: 999999.99, // Max cost price
        weight: 999999.999, // Max weight
        volume: 999999.999, // Max volume
        brand: str_repeat('c', 100), // Max brand length
        manufacturer: str_repeat('d', 100), // Max manufacturer length
        supplier: str_repeat('e', 100), // Max supplier length
        reorderPoint: 1000000, // Max reorder point
        maxStock: 1000000, // Max stock
        notes: str_repeat('f', 2000) // Max notes length
    );

    expect(fn() => $dto->validate())->not->toThrow(\Exception::class);
});
