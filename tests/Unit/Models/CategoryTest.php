<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a category', function () {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'description' => 'Test description',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect($category)->toBeInstanceOf(Category::class);
    expect($category->name)->toBe('Test Category');
    expect($category->description)->toBe('Test description');
    expect($category->is_active)->toBeTrue();
    expect($category->sort_order)->toBe(1);
});

test('can create root category', function () {
    $category = Category::factory()->root()->create();

    expect($category->parent_id)->toBeNull();
    expect($category->isRoot())->toBeTrue();
});

test('can create child category', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    expect($child->parent_id)->toBe($parent->id);
    expect($child->parent->id)->toBe($parent->id);
    expect($child->isRoot())->toBeFalse();
});

test('category has children relationship', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->id)->toBe($child->id);
});

test('category has products relationship', function () {
    $category = Category::factory()->create();
    $product = \App\Models\Product::factory()->withCategory($category)->create();

    expect($category->products)->toHaveCount(1);
    expect($category->products->first()->id)->toBe($product->id);
});

test('active scope returns only active categories', function () {
    Category::factory()->create(['is_active' => true]);
    Category::factory()->create(['is_active' => false]);
    Category::factory()->create(['is_active' => true]);

    $activeCategories = Category::active()->get();

    expect($activeCategories)->toHaveCount(2);
    $activeCategories->each(function ($category) {
        expect($category->is_active)->toBeTrue();
    });
});

test('root scope returns only root categories', function () {
    $root1 = Category::factory()->root()->create();
    $root2 = Category::factory()->root()->create();
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    $rootCategories = Category::root()->get();

    expect($rootCategories)->toHaveCount(2);
    expect($rootCategories->pluck('id'))->toContain($root1->id, $root2->id);
    expect($rootCategories->pluck('id'))->not->toContain($child->id);
});

test('ordered scope sorts by sort_order then name', function () {
    Category::factory()->create(['name' => 'Z Category', 'sort_order' => 1]);
    Category::factory()->create(['name' => 'A Category', 'sort_order' => 1]);
    Category::factory()->create(['name' => 'B Category', 'sort_order' => 2]);

    $orderedCategories = Category::ordered()->get();

    expect($orderedCategories->pluck('name'))->toEqual([
        'A Category',
        'Z Category', 
        'B Category'
    ]);
});

test('full_path attribute works correctly', function () {
    $root = Category::factory()->create(['name' => 'Root']);
    $child1 = Category::factory()->withParent($root)->create(['name' => 'Child 1']);
    $child2 = Category::factory()->withParent($child1)->create(['name' => 'Child 2']);

    expect($root->full_path)->toBe('Root');
    expect($child1->full_path)->toBe('Root / Child 1');
    expect($child2->full_path)->toBe('Root / Child 1 / Child 2');
});

test('has_children method works correctly', function () {
    $parent = Category::factory()->create();
    $childless = Category::factory()->create();
    Category::factory()->withParent($parent)->create();

    expect($parent->hasChildren())->toBeTrue();
    expect($childless->hasChildren())->toBeFalse();
});

test('has_products method works correctly', function () {
    $categoryWithProducts = Category::factory()->create();
    $categoryWithoutProducts = Category::factory()->create();
    \App\Models\Product::factory()->withCategory($categoryWithProducts)->create();

    expect($categoryWithProducts->hasProducts())->toBeTrue();
    expect($categoryWithoutProducts->hasProducts())->toBeFalse();
});

test('descendants method returns all descendants', function () {
    $root = Category::factory()->create();
    $child1 = Category::factory()->withParent($root)->create();
    $child2 = Category::factory()->withParent($root)->create();
    $grandchild = Category::factory()->withParent($child1)->create();

    $descendants = $root->descendants();

    expect($descendants)->toHaveCount(3);
    expect($descendants->pluck('id'))->toContain($child1->id, $child2->id, $grandchild->id);
});

test('total_products_count includes descendant products', function () {
    $root = Category::factory()->create();
    $child = Category::factory()->withParent($root)->create();
    
    \App\Models\Product::factory()->withCategory($root)->create();
    \App\Models\Product::factory()->withCategory($child)->create();
    \App\Models\Product::factory()->count(2)->withCategory($child)->create();

    expect($root->total_products_count)->toBe(4);
    expect($child->total_products_count)->toBe(3);
});

test('category name is unique', function () {
    Category::factory()->create(['name' => 'Test Category']);

    expect(fn() => Category::factory()->create(['name' => 'Test Category']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('can soft delete category if enabled', function () {
    $category = Category::factory()->create();

    $category->delete();

    expect($category->fresh()->trashed())->toBeTrue();
});

test('can restore soft deleted category', function () {
    $category = Category::factory()->create();
    $category->delete();

    $category->restore();

    expect($category->fresh()->trashed())->toBeFalse();
});
