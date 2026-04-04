<?php

use App\DTOs\Category\UpdateCategoryDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('can create valid UpdateCategoryDTO', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Updated Category',
        description: 'Updated description',
        parentId: null,
        isActive: false,
        sortOrder: 5,
        categoryId: 1
    );

    expect($dto->getName())->toBe('Updated Category');
    expect($dto->getDescription())->toBe('Updated description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getSortOrder())->toBe(5);
    expect($dto->getCategoryId())->toBe(1);
});

test('can create UpdateCategoryDTO with parent', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Child Category Updated',
        description: 'Child description updated',
        parentId: 5,
        isActive: true,
        sortOrder: 10,
        categoryId: 2
    );

    expect($dto->getName())->toBe('Child Category Updated');
    expect($dto->getParentId())->toBe(5);
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(10);
    expect($dto->getCategoryId())->toBe(2);
});

test('can create UpdateCategoryDTO with minimal data', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Minimal Update',
        categoryId: 1
    );

    expect($dto->getName())->toBe('Minimal Update');
    expect($dto->getDescription())->toBeNull();
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeNull(); // Optional field
    expect($dto->getSortOrder())->toBeNull(); // Optional field
    expect($dto->getCategoryId())->toBe(1);
});

test('validation passes with valid data', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Valid Category',
        description: 'Valid description',
        parentId: null,
        isActive: true,
        sortOrder: 5,
        categoryId: 1
    );

    expect(fn() => $dto->validate())->not->toThrow();
});

test('validation fails with empty name', function () {
    $dto = new UpdateCategoryDTO(
        name: '',
        description: 'Valid description',
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with name too long', function () {
    $dto = new UpdateCategoryDTO(
        name: str_repeat('a', 201), // 201 characters
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with description too long', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Valid Category',
        description: str_repeat('a', 1001), // 1001 characters
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid parent id', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Valid Category',
        parentId: 999, // Non-existent parent
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with invalid sort order', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Valid Category',
        sortOrder: -1, // Negative value
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation fails with sort order too high', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Valid Category',
        sortOrder: 1000001, // Too high
        categoryId: 1
    );

    expect(fn() => $dto->validate())
        ->toThrow(ValidationException::class);
});

test('validation rules are correct', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1,
        categoryId: 1
    );

    $rules = $dto->rules();

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('description');
    expect($rules)->toHaveKey('parent_id');
    expect($rules)->toHaveKey('is_active');
    expect($rules)->toHaveKey('sort_order');
    expect($rules)->toHaveKey('category_id');

    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('max:200');
    expect($rules['description'])->toContain('nullable');
    expect($rules['description'])->toContain('max:1000');
    expect($rules['parent_id'])->toContain('nullable');
    expect($rules['parent_id'])->toContain('integer');
    expect($rules['parent_id'])->toContain('exists:categories,id');
    expect($rules['is_active'])->toContain('boolean');
    expect($rules['sort_order'])->toContain('nullable');
    expect($rules['sort_order'])->toContain('integer');
    expect($rules['sort_order'])->toContain('min:0');
    expect($rules['sort_order'])->toContain('max:1000000');
    expect($rules['category_id'])->toContain('required');
    expect($rules['category_id'])->toContain('integer');
    expect($rules['category_id'])->toContain('exists:categories,id');
});

test('validation messages are correct', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1,
        categoryId: 1
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
    expect($messages)->toHaveKey('category_id.required');
    expect($messages)->toHaveKey('category_id.exists');

    expect($messages['name.required'])->toBe('The category name is required.');
    expect($messages['name.max'])->toBe('The category name may not be greater than 200 characters.');
    expect($messages['description.max'])->toBe('The description may not be greater than 1000 characters.');
    expect($messages['parent_id.exists'])->toBe('The selected parent category does not exist.');
    expect($messages['category_id.required'])->toBe('Category ID is required.');
    expect($messages['category_id.exists'])->toBe('The category being updated does not exist.');
});

test('toArray returns correct data', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: 5,
        isActive: true,
        sortOrder: 10,
        categoryId: 1
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('name');
    expect($array)->toHaveKey('description');
    expect($array)->toHaveKey('parent_id');
    expect($array)->toHaveKey('is_active');
    expect($array)->toHaveKey('sort_order');
    expect($array)->toHaveKey('category_id');

    expect($array['name'])->toBe('Test Category');
    expect($array['description'])->toBe('Test description');
    expect($array['parent_id'])->toBe(5);
    expect($array['is_active'])->toBeTrue();
    expect($array['sort_order'])->toBe(10);
    expect($array['category_id'])->toBe(1);
});

test('toJson returns correct JSON', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: null,
        isActive: true,
        sortOrder: 1,
        categoryId: 1
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
        'category_id' => 1,
    ];

    $dto = UpdateCategoryDTO::fromArray($data);

    expect($dto->getName())->toBe('Array Category');
    expect($dto->getDescription())->toBe('Array description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getSortOrder())->toBe(5);
    expect($dto->getCategoryId())->toBe(1);
});

test('can create from JSON', function () {
    $json = json_encode([
        'name' => 'JSON Category',
        'description' => 'JSON description',
        'parent_id' => null,
        'is_active' => true,
        'sort_order' => 3,
        'category_id' => 1,
    ]);

    $dto = UpdateCategoryDTO::fromJson($json);

    expect($dto->getName())->toBe('JSON Category');
    expect($dto->getDescription())->toBe('JSON description');
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(3);
    expect($dto->getCategoryId())->toBe(1);
});

test('helper methods work correctly', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: 5,
        isActive: true,
        sortOrder: 10,
        categoryId: 1
    );

    expect($dto->getParentId())->toBe(5);
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(10);
    expect($dto->getCategoryId())->toBe(1);
});

test('helper methods work for root category', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Root Category',
        description: 'Root description',
        parentId: null,
        isActive: true,
        sortOrder: 0,
        categoryId: 1
    );

    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeTrue();
    expect($dto->getSortOrder())->toBe(0);
    expect($dto->getCategoryId())->toBe(1);
});

test('helper methods work for inactive category', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Inactive Category',
        description: 'Inactive description',
        parentId: null,
        isActive: false,
        sortOrder: 0,
        categoryId: 1
    );

    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getCategoryId())->toBe(1);
});

test('validation skips in unit tests', function () {
    // Define constant to simulate unit test environment
    if (!defined('PHPUNIT_RUNNING')) {
        define('PHPUNIT_RUNNING', true);
    }

    $dto = new UpdateCategoryDTO(
        name: '', // Invalid name
        description: '',
        parentId: 999, // Invalid parent
        isActive: true,
        sortOrder: -1, // Invalid sort order
        categoryId: 1
    );

    // Should not throw exception in unit test mode
    expect($dto->validate())->toBeNull(); // Validation should be skipped
});

test('can update only specific fields', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Only Name Updated',
        categoryId: 1
    );

    expect($dto->getName())->toBe('Only Name Updated');
    expect($dto->getDescription())->toBeNull();
    expect($dto->getParentId())->toBeNull();
    expect($dto->getIsActive())->toBeNull();
    expect($dto->getSortOrder())->toBeNull();
    expect($dto->getCategoryId())->toBe(1);
});

test('can update only status', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Same Name',
        isActive: false,
        categoryId: 1
    );

    expect($dto->getName())->toBe('Same Name');
    expect($dto->getIsActive())->toBeFalse();
    expect($dto->getCategoryId())->toBe(1);
});

test('can update only sort order', function () {
    $dto = new UpdateCategoryDTO(
        name: 'Same Name',
        sortOrder: 15,
        categoryId: 1
    );

    expect($dto->getName())->toBe('Same Name');
    expect($dto->getSortOrder())->toBe(15);
    expect($dto->getCategoryId())->toBe(1);
});
