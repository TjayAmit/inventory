<?php

use App\Models\Category;
use App\Services\CategoryService;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Mockery\Adapter\Phpunit\Mockery;

beforeEach(function () {
    $this->repository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->service = new CategoryService($this->repository);
});

test('can create category', function () {
    $dto = new \App\DTOs\Category\CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        isActive: true,
        sortOrder: 1
    );

    $expectedCategory = new Category([
        'id' => 1,
        'name' => 'Test Category',
        'description' => 'Test description',
        'is_active' => true,
        'sort_order' => 1
    ]);

    $this->repository
        ->shouldReceive('nameExists')
        ->with('Test Category')
        ->andReturn(false);

    $this->repository
        ->shouldReceive('create')
        ->with($dto)
        ->andReturn($expectedCategory);

    $result = $this->service->createCategory($dto);

    expect($result)->toBeInstanceOf(\App\DTOs\Category\CategoryResponseDTO::class);
    expect($result->getName())->toBe('Test Category');
});

test('cannot create category with existing name', function () {
    $dto = new \App\DTOs\Category\CreateCategoryDTO(
        name: 'Existing Category',
        description: 'Test description',
        isActive: true,
        sortOrder: 1
    );

    $this->repository
        ->shouldReceive('nameExists')
        ->with('Existing Category')
        ->andReturn(true);

    expect(fn() => $this->service->createCategory($dto))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('cannot create category with invalid parent', function () {
    $dto = new \App\DTOs\Category\CreateCategoryDTO(
        name: 'Test Category',
        description: 'Test description',
        parentId: 999,
        isActive: true,
        sortOrder: 1
    );

    $this->repository
        ->shouldReceive('nameExists')
        ->andReturn(false);

    $this->repository
        ->shouldReceive('findById')
        ->with(999)
        ->andReturn(null);

    expect(fn() => $this->service->createCategory($dto))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('can update category', function () {
    $category = Category::factory()->create();

    $dto = new \App\DTOs\Category\UpdateCategoryDTO(
        name: 'Updated Category',
        description: 'Updated description',
        isActive: false,
        sortOrder: 5,
        categoryId: $category->id
    );

    $expectedCategory = new Category([
        'id' => $category->id,
        'name' => 'Updated Category',
        'description' => 'Updated description',
        'is_active' => false,
        'sort_order' => 5
    ]);

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('nameExists')
        ->with('Updated Category', $category->id)
        ->andReturn(false);

    $this->repository
        ->shouldReceive('update')
        ->with($category->id, $dto)
        ->andReturn($expectedCategory);

    $result = $this->service->updateCategory($category->id, $dto);

    expect($result)->toBeInstanceOf(\App\DTOs\Category\CategoryResponseDTO::class);
    expect($result->getName())->toBe('Updated Category');
});

test('cannot update non-existing category', function () {
    $dto = new \App\DTOs\Category\UpdateCategoryDTO(
        name: 'Updated Category',
        description: 'Updated description',
        isActive: false,
        sortOrder: 5,
        categoryId: 999
    );

    $this->repository
        ->shouldReceive('findById')
        ->with(999)
        ->andReturn(null);

    expect(fn() => $this->service->updateCategory(999, $dto))
        ->toThrow(\InvalidArgumentException::class, 'Category not found.');
});

test('can delete category', function () {
    $category = Category::factory()->create();

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('hasChildren')
        ->with($category->id)
        ->andReturn(false);

    $this->repository
        ->shouldReceive('hasProducts')
        ->with($category->id)
        ->andReturn(false);

    $this->repository
        ->shouldReceive('delete')
        ->with($category->id)
        ->andReturn(true);

    $result = $this->service->deleteCategory($category->id);

    expect($result)->toBeTrue();
});

test('cannot delete category with children', function () {
    $category = Category::factory()->create();

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('hasChildren')
        ->with($category->id)
        ->andReturn(true);

    expect(fn() => $this->service->deleteCategory($category->id))
        ->toThrow(\InvalidArgumentException::class, 'Cannot delete category that has subcategories.');
});

test('cannot delete category with products', function () {
    $category = Category::factory()->create();

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('hasChildren')
        ->with($category->id)
        ->andReturn(false);

    $this->repository
        ->shouldReceive('hasProducts')
        ->with($category->id)
        ->andReturn(true);

    expect(fn() => $this->service->deleteCategory($category->id))
        ->toThrow(\InvalidArgumentException::class, 'Cannot delete category that has products.');
});

test('can get paginated categories', function () {
    $categories = Category::factory()->count(15)->get();
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator($categories, 15, 15);

    $this->repository
        ->shouldReceive('paginate')
        ->with(15, ['parent', 'children'])
        ->andReturn($paginator);

    $result = $this->service->getPaginatedCategories(15);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->total())->toBe(15);
});

test('can get active categories', function () {
    $categories = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Active 1']),
        Category::factory()->make(['id' => 2, 'name' => 'Active 2']),
    ]);

    $this->repository
        ->shouldReceive('getActiveOrdered')
        ->andReturn($categories);

    $result = $this->service->getActiveCategories();

    expect($result)->toHaveCount(2);
    expect($result->first()->getName())->toBe('Active 1');
});

test('can get category tree', function () {
    $tree = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Root 1']),
        Category::factory()->make(['id' => 2, 'name' => 'Root 2']),
    ]);

    $this->repository
        ->shouldReceive('getCategoryTree')
        ->andReturn($tree);

    $result = $this->service->getCategoryTree();

    expect($result)->toHaveCount(2);
    expect($result->first()->getName())->toBe('Root 1');
});

test('can get root categories', function () {
    $roots = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Root 1']),
        Category::factory()->make(['id' => 2, 'name' => 'Root 2']),
    ]);

    $this->repository
        ->shouldReceive('getRootCategories')
        ->andReturn($roots);

    $result = $this->service->getRootCategories();

    expect($result)->toHaveCount(2);
    expect($result->first()->getName())->toBe('Root 1');
});

test('can get child categories', function () {
    $children = collect([
        Category::factory()->make(['id' => 2, 'name' => 'Child 1']),
        Category::factory()->make(['id' => 3, 'name' => 'Child 2']),
    ]);

    $this->repository
        ->shouldReceive('getChildCategories')
        ->with(1)
        ->andReturn($children);

    $result = $this->service->getChildCategories(1);

    expect($result)->toHaveCount(2);
    expect($result->first()->getName())->toBe('Child 1');
});

test('can get category by id', function () {
    $category = Category::factory()->make(['id' => 1, 'name' => 'Test Category']);

    $this->repository
        ->shouldReceive('findById')
        ->with(1, ['parent', 'children'])
        ->andReturn($category);

    $result = $this->service->getCategoryById(1);

    expect($result)->toBeInstanceOf(\App\DTOs\Category\CategoryResponseDTO::class);
    expect($result->getName())->toBe('Test Category');
});

test('returns null for non-existing category', function () {
    $this->repository
        ->shouldReceive('findById')
        ->with(999, ['parent', 'children'])
        ->andReturn(null);

    $result = $this->service->getCategoryById(999);

    expect($result)->toBeNull();
});

test('can search categories', function () {
    $results = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Electronics']),
        Category::factory()->make(['id' => 2, 'name' => 'Electronic Gadgets']),
    ]);

    $this->repository
        ->shouldReceive('search')
        ->with('Electronic', 10)
        ->andReturn($results);

    $searchResults = $this->service->searchCategories('Electronic', 10);

    expect($searchResults)->toHaveCount(2);
    expect($searchResults->first()->getName())->toBe('Electronics');
});

test('can get categories for dropdown', function () {
    $categories = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Category 1']),
        Category::factory()->make(['id' => 2, 'name' => 'Category 2']),
    ]);

    $this->repository
        ->shouldReceive('getForDropdown')
        ->with(null)
        ->andReturn($categories);

    $result = $this->service->getCategoriesForDropdown();

    expect($result)->toHaveCount(2);
    expect($result->first()->id)->toBe(1);
});

test('can get categories for dropdown excluding id', function () {
    $categories = collect([
        Category::factory()->make(['id' => 2, 'name' => 'Category 2']),
    ]);

    $this->repository
        ->shouldReceive('getForDropdown')
        ->with(1)
        ->andReturn($categories);

    $result = $this->service->getCategoriesForDropdown(1);

    expect($result)->toHaveCount(1);
    expect($result->first()->id)->toBe(2);
});

test('can get categories with product counts', function () {
    $categories = collect([
        Category::factory()->make(['id' => 1, 'name' => 'Category 1', 'products_count' => 5]),
        Category::factory()->make(['id' => 2, 'name' => 'Category 2', 'products_count' => 3]),
    ]);

    $this->repository
        ->shouldReceive('getWithProductCounts')
        ->andReturn($categories);

    $result = $this->service->getCategoriesWithProductCounts();

    expect($result)->toHaveCount(2);
    expect($result->first()->getProductsCount())->toBe(5);
});

test('can update sort order', function () {
    $categoryIds = [1, 2, 3];

    $this->repository
        ->shouldReceive('updateSortOrder')
        ->with($categoryIds)
        ->andReturn(true);

    $result = $this->service->updateCategorySortOrder($categoryIds);

    expect($result)->toBeTrue();
});

test('can toggle category status', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('hasProducts')
        ->with($category->id)
        ->andReturn(false);

    $this->repository
        ->shouldReceive('update')
        ->andReturn($category);

    $result = $this->service->toggleCategoryStatus($category->id);

    expect($result)->toBeInstanceOf(\App\DTOs\Category\CategoryResponseDTO::class);
});

test('cannot deactivate category with products', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('hasProducts')
        ->with($category->id)
        ->andReturn(true);

    expect(fn() => $this->service->toggleCategoryStatus($category->id))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('can move category', function () {
    $category = Category::factory()->create();
    $newParent = Category::factory()->create();

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('getDescendants')
        ->with($category->id)
        ->andReturn(collect());

    $this->repository
        ->shouldReceive('update')
        ->andReturn($category);

    $result = $this->service->moveCategory($category->id, $newParent->id);

    expect($result)->toBeInstanceOf(\App\DTOs\Category\CategoryResponseDTO::class);
});

test('cannot move category to its own descendant', function () {
    $category = Category::factory()->create();
    $descendant = Category::factory()->create();

    $this->repository
        ->shouldReceive('findById')
        ->with($category->id)
        ->andReturn($category);

    $this->repository
        ->shouldReceive('getDescendants')
        ->with($category->id)
        ->andReturn(collect([$descendant]));

    expect(fn() => $this->service->moveCategory($category->id, $descendant->id))
        ->toThrow(\InvalidArgumentException::class, 'Cannot move category to its own descendant.');
});

test('can get category statistics', function () {
    $this->repository
        ->shouldReceive('getModel')
        ->andReturn(new Category());

    $this->repository
        ->shouldReceive('countByStatus')
        ->with(true)
        ->andReturn(10);

    $this->repository
        ->shouldReceive('countByStatus')
        ->with(false)
        ->andReturn(2);

    $this->repository
        ->shouldReceive('getRootCategories')
        ->andReturn(collect([Category::factory()->make(), Category::factory()->make()]));

    $stats = $this->service->getCategoryStatistics();

    expect($stats['total'])->toBe(12);
    expect($stats['active'])->toBe(10);
    expect($stats['inactive'])->toBe(2);
    expect($stats['root'])->toBe(2);
    expect($stats['active_percentage'])->toBe(83.33);
});

test('can get category descendants', function () {
    $descendants = collect([
        Category::factory()->make(['id' => 2, 'name' => 'Child 1']),
        Category::factory()->make(['id' => 3, 'name' => 'Grandchild']),
    ]);

    $this->repository
        ->shouldReceive('getDescendants')
        ->with(1)
        ->andReturn($descendants);

    $result = $this->service->getCategoryDescendants(1);

    expect($result)->toHaveCount(2);
    expect($result->first()->getName())->toBe('Child 1');
});
