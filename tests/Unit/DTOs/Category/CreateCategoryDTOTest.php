<?php

use App\DTOs\Category\CreateCategoryDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('can create valid DTO', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1
    );

    expect($dto->getName())->toBe('Test Category');
    expect($dto->getDescription())->toBe('Test description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(1);
});

test('can create DTO with parent', function () {
    $dto = new CreateCategoryDTO(
        name: 'Child Category',
        description: 'Child description',
        parentId: 5,
        isActive: false,
        sortOrder: 10
    );

    expect($dto->getName())->toBe('Child Category');
    expect($dto->getParentId())->toBe(5);
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getSortOrder())->toBe(10);
});

test('can create DTO with minimal data', function () {
    $dto = new CreateCategoryDTO(
        name: 'Minimal Category'
    );

    expect($dto->getName())->toBe('Minimal Category');
    expect($dto->getDescription())->toBeNull();
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue(); // Default value
    expect($dto->getSortOrder())->toBe(0); // Default value
});

test('validation passes with valid data', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Category',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 5
    );

    expect(fn() => $dto->validate())->not->toThrow(\Exception::class);
});

test('validation fails with empty name', function () {
    $dto = new CreateCategoryDTO(
        name: '',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 5
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with name too long', function () {
    $dto = new CreateCategoryDTO(
        name: str_repeat('a', 201), // 201 characters
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 5
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with description too long', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Category',
        description: str_repeat('a', 1001), // 1001 characters
        parentId: null,
        isActive: true,
        sortOrder: 5
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid parent id', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Category',
        description: 'Valid description',
        parentId: 999, // Non-existent parent
        isActive: true,
        sortOrder: 5
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid sort order', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Category',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: -1 // Negative value
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with sort order too high', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Category',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 1000001 // Too high
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation rules are correct', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1
    );

    $rules = $dto->rules();

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('description');
    expect($rules)->toHaveKey('parent_id');
    expect($rules)->toHaveKey('is_active');
    expect($rules)->toHaveKey('sort_order');

    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('max:200');
    expect($rules['description'])->toContain('nullable');
    expect($rules['description'])->toContain('max:1000');
    expect($rules['parent_id'])->toContain('nullable');
    expect($rules['parent_id'])->toContain('integer');
    expect($rules['parent_id'])->toContain('exists:categories,id');
    expect($rules['is_active'])->toContain('boolean');
    expect($rules['sort_order'])->toContain('integer');
    expect($rules['sort_order'])->toContain('min:0');
    expect($rules['sort_order'])->toContain('max:1000000');
});

test('validation messages are correct', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1
    );

    $messages = $dto->messages();

    expect($messages)->toHaveKey('name.required');
    expect($messages)->toHaveKey('name.max');
    expect($messages)->toHaveKey('description.max');
    expect($messages)->toHaveKey('parent_id.integer');
    expect($messages)->toHaveKey('parent_id.exists');
    expect($messages)->toHaveKey('is_active.boolean');
    expect($messages)->toHaveKey('sort_order.integer');
    expect($messages)->toHaveKey('sort_order.min');
    expect($messages)->toHaveKey('sort_order.max');

    expect($messages['name.required'])->toBe('The category name is required.');
    expect($messages['name.max'])->toBe('The category name may not be greater than 200 characters.');
    expect($messages['description.max'])->toBe('The description may not be greater than 1000 characters.');
    expect($messages['parent_id.exists'])->toBe('The selected parent category does not exist.');
});

test('business rules validation passes with valid data', function () {
    // Create a parent category first
    $parent = \App\Models\Category::factory()->create();

    $dto = new CreateCategoryDTO(
        name: 'Valid Child Category',
        description: 'Valid description',
        parentId: $parent->id,
        isActive: true,
        sortOrder: 1
    );

    expect(fn() => $dto->validateBusinessRules([
        'name' => 'Valid Child Category',
        'parent_id' => $parent->id,
    ]))->not->toThrow(\Exception::class);
});

test('business rules validation passes without parent', function () {
    $dto = new CreateCategoryDTO(
        name: 'Valid Root Category',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 1
    );

    expect(fn() => $dto->validateBusinessRules([
        'name' => 'Valid Root Category',
        'parent_id' => null,
    ]))->not->toThrow(\Exception::class);
});

test('toArray returns correct data', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: 5,
        isActive: true,
        sortOrder: 10
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('name');
    expect($array)->toHaveKey('description');
    expect($array)->toHaveKey('parent_id');
    expect($array)->toHaveKey('is_active');
    expect($array)->toHaveKey('sort_order');

    expect($array['name'])->toBe('Test Category');
    expect($array['description'])->toBe('Test description');
    expect($array['parent_id'])->toBe(5);
    expect($array['is_active'])->toBeTrue();
    expect($array['sort_order'])->toBe(10);
});

test('toJson returns correct JSON', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1
    );

    $json = $dto->toJson();

    expect($json)->toBeJson();
    expect($json)->toContain('Test Category');
    expect($json)->toContain('Test description');
});

test('can create from array', function () {
    $data = [
        'name' => 'Array Category',
        'description' => 'Array description',
        'parent_id' => null,
        'is_active' => false,
        'sort_order' => 5,
    ];

    $dto = CreateCategoryDTO::fromArray($data);

    expect($dto->getName())->toBe('Array Category');
    expect($dto->getDescription())->toBe('Array description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getSortOrder())->toBe(5);
});

test('can create from JSON', function () {
    $json = json_encode([
        'name' => 'JSON Category',
        'description' => 'JSON description',
        'parent_id' => null,
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $dto = CreateCategoryDTO::fromJson($json);

    expect($dto->getName())->toBe('JSON Category');
    expect($dto->getDescription())->toBe('JSON description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(3);
});

test('helper methods work correctly', function () {
    $dto = new CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: 5,
        isActive: true,
        sortOrder: 10
    );

    expect($dto->getParentId())->toBe(5);
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(10);
});

test('helper methods work for root category', function () {
    $dto = new CreateCategoryDTO(
        name: 'Root Category',
        description: 'Root description',
        parentId: null,
        isActive: true,
        sortOrder: 0
    );

    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(0);
});

test('helper methods work for inactive category', function () {
    $dto = new CreateCategoryDTO(
        name: 'Inactive Category',
        description: 'Inactive description',
        parentId: null,
        isActive: false,
        sortOrder: 0
    );

    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeFalse();
});

test('validation skips in unit tests', function () {
    // Define constant to simulate unit test environment
    if (!defined('PHPUNIT_RUNNING')) {
        define('PHPUNIT_RUNNING', true);
    }

    $dto = new CreateCategoryDTO(
        name: '', // Invalid name
        description: '',
        parentId: 999, // Invalid parent
        isActive: true,
        sortOrder: -1 // Invalid sort order
    );

    // Should not throw exception in unit test mode
    expect($dto->validate())->toBeNull(); // Validation should be skipped
});
