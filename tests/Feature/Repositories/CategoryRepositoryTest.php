<?php

use App\Models\Category;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new CategoryRepository(new Category());
});

test('can create category', function () {
    $dto = new \App\DTOs\Category\CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        isActive: true,
        sortOrder: 1
    );

    $category = $this->repository->create($dto);

    expect($category)->toBeInstanceOf(Category::class);
    expect($category->name)->toBe('Test Category');
    expect($category->description)->toBe('Test description');
    expect($category->is_active)->toBeTrue();
    expect($category->sort_order)->toBe(1);
});

test('can update category', function () {
    $category = Category::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original Description',
    ]);

    $dto = new \App\DTOs\Category\UpdateCategoryDTO(
        name: 'Updated Name',
        description: 'Updated Description',
        isActive: false,
        sortOrder: 5,
        categoryId: $category->id
    );

    $updated = $this->repository->update($category->id, $dto);

    expect($updated->name)->toBe('Updated Name');
    expect($updated->description)->toBe('Updated Description');
    expect($updated->is_active)->toBeFalse();
    expect($updated->sort_order)->toBe(5);
});

test('can find category by id', function () {
    $category = Category::factory()->create();

    $found = $this->repository->findById($category->id);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($category->id);
});

test('can find category by name', function () {
    $category = Category::factory()->create(['name' => 'Test Category']);

    $found = $this->repository->findByName('Test Category');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($category->id);
});

test('can delete category', function () {
    $category = Category::factory()->create();

    $result = $this->repository->delete($category->id);

    expect($result)->toBeTrue();
    expect(Category::find($category->id))->toBeNull();
});

test('cannot delete category with children', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    expect(fn() => $this->repository->delete($parent->id))
        ->toThrow(\InvalidArgumentException::class, 'Cannot delete category that has subcategories.');
});

test('cannot delete category with products', function () {
    $category = Category::factory()->create();
    \App\Models\Product::factory()->withCategory($category)->create();

    expect(fn() => $this->repository->delete($category->id))
        ->toThrow(\InvalidArgumentException::class, 'Cannot delete category that has products.');
});

test('name_exists returns true for existing name', function () {
    Category::factory()->create(['name' => 'Test Category']);

    $exists = $this->repository->nameExists('Test Category');

    expect($exists)->toBeTrue();
});

test('name_exists returns false for non-existing name', function () {
    $exists = $this->repository->nameExists('Non-existing Category');

    expect($exists)->toBeFalse();
});

test('name_exists excludes given id', function () {
    $category = Category::factory()->create(['name' => 'Test Category']);

    $exists = $this->repository->nameExists('Test Category', $category->id);

    expect($exists)->toBeFalse();
});

test('can get paginated categories', function () {
    Category::factory()->count(20)->create();

    $paginated = $this->repository->paginate(10);

    expect($paginated)->toHaveCount(10);
    expect($paginated->total())->toBe(20);
});

test('can get active ordered categories', function () {
    Category::factory()->create(['is_active' => true, 'sort_order' => 2]);
    Category::factory()->create(['is_active' => false, 'sort_order' => 1]);
    Category::factory()->create(['is_active' => true, 'sort_order' => 1]);

    $categories = $this->repository->getActiveOrdered();

    expect($categories)->toHaveCount(2);
    expect($categories->first()->sort_order)->toBe(1);
    expect($categories->last()->sort_order)->toBe(2);
});

test('can get root categories', function () {
    $root1 = Category::factory()->root()->create();
    $root2 = Category::factory()->root()->create();
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    $roots = $this->repository->getRootCategories();

    expect($roots)->toHaveCount(2);
    expect($roots->pluck('id'))->toContain($root1->id, $root2->id);
});

test('can get child categories', function () {
    $parent = Category::factory()->create();
    $child1 = Category::factory()->withParent($parent)->create();
    $child2 = Category::factory()->withParent($parent)->create();
    $other = Category::factory()->create();

    $children = $this->repository->getChildCategories($parent->id);

    expect($children)->toHaveCount(2);
    expect($children->pluck('id'))->toContain($child1->id, $child2->id);
});

test('can get categories with product counts', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    \App\Models\Product::factory()->count(3)->withCategory($category1)->create();
    \App\Models\Product::factory()->count(2)->withCategory($category2)->create();

    $categories = $this->repository->getWithProductCounts();

    expect($categories)->toHaveCount(2);
    expect($categories->find($category1->id)->products_count)->toBe(3);
    expect($categories->find($category2->id)->products_count)->toBe(2);
});

test('can get category tree', function () {
    $root = Category::factory()->create();
    $child1 = Category::factory()->withParent($root)->create();
    $child2 = Category::factory()->withParent($root)->create();
    $grandchild = Category::factory()->withParent($child1)->create();

    $tree = $this->repository->getCategoryTree();

    expect($tree)->toHaveCount(1);
    expect($tree->first()->id)->toBe($root->id);
    expect($tree->first()->children)->toHaveCount(2);
});

test('can get descendants', function () {
    $root = Category::factory()->create();
    $child1 = Category::factory()->withParent($root)->create();
    $child2 = Category::factory()->withParent($root)->create();
    $grandchild = Category::factory()->withParent($child1)->create();

    $descendants = $this->repository->getDescendants($root->id);

    expect($descendants)->toHaveCount(3);
    expect($descendants->pluck('id'))->toContain($child1->id, $child2->id, $grandchild->id);
});

test('has_children returns correct value', function () {
    $parent = Category::factory()->create();
    $childless = Category::factory()->create();
    Category::factory()->withParent($parent)->create();

    expect($this->repository->hasChildren($parent->id))->toBeTrue();
    expect($this->repository->hasChildren($childless->id))->toBeFalse();
});

test('has_products returns correct value', function () {
    $categoryWithProducts = Category::factory()->create();
    $categoryWithoutProducts = Category::factory()->create();
    \App\Models\Product::factory()->withCategory($categoryWithProducts)->create();

    expect($this->repository->hasProducts($categoryWithProducts->id))->toBeTrue();
    expect($this->repository->hasProducts($categoryWithoutProducts->id))->toBeFalse();
});

test('can update sort order', function () {
    $category1 = Category::factory()->create(['sort_order' => 5]);
    $category2 = Category::factory()->create(['sort_order' => 3]);
    $category3 = Category::factory()->create(['sort_order' => 1]);

    $result = $this->repository->updateSortOrder([$category3->id, $category2->id, $category1->id]);

    expect($result)->toBeTrue();
    
    $category1->refresh();
    $category2->refresh();
    $category3->refresh();
    
    expect($category3->sort_order)->toBe(0);
    expect($category2->sort_order)->toBe(1);
    expect($category1->sort_order)->toBe(2);
});

test('can search categories', function () {
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Books']);
    Category::factory()->create(['name' => 'Electronic Gadgets']);

    $results = $this->repository->search('Electronic');

    expect($results)->toHaveCount(2);
    $results->each(function ($category) {
        expect($category->name)->toContain('Electronic');
    });
});

test('can get categories for dropdown', function () {
    Category::factory()->count(3)->create();

    $categories = $this->repository->getForDropdown();

    expect($categories)->toHaveCount(3);
    $categories->each(function ($category) {
        expect($category)->toHaveKeys(['id', 'name', 'parent_id']);
    });
});

test('get_for_dropdown_excludes_given_id', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $categories = $this->repository->getForDropdown($category1->id);

    expect($categories)->toHaveCount(1);
    expect($categories->first()->id)->toBe($category2->id);
});

test('can count by status', function () {
    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);

    $activeCount = $this->repository->countByStatus(true);
    $inactiveCount = $this->repository->countByStatus(false);

    expect($activeCount)->toBe(3);
    expect($inactiveCount)->toBe(2);
});

test('can get categories with full path', function () {
    $root = Category::factory()->create(['name' => 'Root']);
    $child = Category::factory()->withParent($root)->create(['name' => 'Child']);

    $categories = $this->repository->getWithFullPath();

    expect($categories)->toHaveCount(2);
    expect($categories->find($root->id)->full_path)->toBe('Root');
    expect($categories->find($child->id)->full_path)->toBe('Root / Child');
});
