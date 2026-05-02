<?php

use App\Models\Category;
use App\Models\User;
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new CategoryPolicy();
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->storeManager = User::factory()->create()->assignRole('store_manager');
    $this->warehouseStaff = User::factory()->create()->assignRole('warehouse_staff');
    $this->user = User::factory()->create()->assignRole('user');
});

test('any authenticated user can view any categories', function () {
    expect($this->policy->viewAny($this->user))->toBeTrue();
    expect($this->policy->viewAny($this->admin))->toBeTrue();
    expect($this->policy->viewAny($this->storeManager))->toBeTrue();
    expect($this->policy->viewAny($this->warehouseStaff))->toBeTrue();
});

test('any authenticated user can view category', function () {
    $category = Category::factory()->create();

    expect($this->policy->view($this->user, $category))->toBeTrue();
    expect($this->policy->view($this->admin, $category))->toBeTrue();
    expect($this->policy->view($this->storeManager, $category))->toBeTrue();
    expect($this->policy->view($this->warehouseStaff, $category))->toBeTrue();
});

test('only admin and store manager can create categories', function () {
    expect($this->policy->create($this->admin))->toBeTrue();
    expect($this->policy->create($this->storeManager))->toBeTrue();
    expect($this->policy->create($this->warehouseStaff))->toBeFalse();
    expect($this->policy->create($this->user))->toBeFalse();
});

test('only admin and store manager can update categories', function () {
    $category = Category::factory()->create();

    expect($this->policy->update($this->admin, $category))->toBeTrue();
    expect($this->policy->update($this->storeManager, $category))->toBeTrue();
    expect($this->policy->update($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->update($this->user, $category))->toBeFalse();
});

test('only admin can delete categories', function () {
    $category = Category::factory()->create();

    expect($this->policy->delete($this->admin, $category))->toBeTrue();
    expect($this->policy->delete($this->storeManager, $category))->toBeFalse();
    expect($this->policy->delete($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->delete($this->user, $category))->toBeFalse();
});

test('admin cannot delete category with children', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->withParent($parent)->create();

    expect($this->policy->delete($this->admin, $parent))->toBeFalse();
});

test('admin cannot delete category with products', function () {
    $category = Category::factory()->create();
    \App\Models\Product::factory()->withCategory($category)->create();

    expect($this->policy->delete($this->admin, $category))->toBeFalse();
});

test('only admin can restore categories', function () {
    $category = Category::factory()->create();

    expect($this->policy->restore($this->admin, $category))->toBeTrue();
    expect($this->policy->restore($this->storeManager, $category))->toBeFalse();
    expect($this->policy->restore($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->restore($this->user, $category))->toBeFalse();
});

test('only admin can force delete categories', function () {
    $category = Category::factory()->create();

    expect($this->policy->forceDelete($this->admin, $category))->toBeTrue();
    expect($this->policy->forceDelete($this->storeManager, $category))->toBeFalse();
    expect($this->policy->forceDelete($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->forceDelete($this->user, $category))->toBeFalse();
});

test('only admin and store manager can manage hierarchy', function () {
    expect($this->policy->manageHierarchy($this->admin))->toBeTrue();
    expect($this->policy->manageHierarchy($this->storeManager))->toBeTrue();
    expect($this->policy->manageHierarchy($this->warehouseStaff))->toBeFalse();
    expect($this->policy->manageHierarchy($this->user))->toBeFalse();
});

test('only admin and store manager can move categories', function () {
    $category = Category::factory()->create();

    expect($this->policy->move($this->admin, $category))->toBeTrue();
    expect($this->policy->move($this->storeManager, $category))->toBeTrue();
    expect($this->policy->move($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->move($this->user, $category))->toBeFalse();
});

test('only admin and store manager can toggle category status', function () {
    $category = Category::factory()->create();

    expect($this->policy->toggleStatus($this->admin, $category))->toBeTrue();
    expect($this->policy->toggleStatus($this->storeManager, $category))->toBeTrue();
    expect($this->policy->toggleStatus($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->toggleStatus($this->user, $category))->toBeFalse();
});

test('only admin and store manager can update sort order', function () {
    expect($this->policy->updateSortOrder($this->admin))->toBeTrue();
    expect($this->policy->updateSortOrder($this->storeManager))->toBeTrue();
    expect($this->policy->updateSortOrder($this->warehouseStaff))->toBeFalse();
    expect($this->policy->updateSortOrder($this->user))->toBeFalse();
});

test('only admin and store manager can view statistics', function () {
    expect($this->policy->viewStatistics($this->admin))->toBeTrue();
    expect($this->policy->viewStatistics($this->storeManager))->toBeTrue();
    expect($this->policy->viewStatistics($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewStatistics($this->user))->toBeFalse();
});

test('only admin and store manager can export categories', function () {
    expect($this->policy->export($this->admin))->toBeTrue();
    expect($this->policy->export($this->storeManager))->toBeTrue();
    expect($this->policy->export($this->warehouseStaff))->toBeFalse();
    expect($this->policy->export($this->user))->toBeFalse();
});

test('only admin can import categories', function () {
    expect($this->policy->import($this->admin))->toBeTrue();
    expect($this->policy->import($this->storeManager))->toBeFalse();
    expect($this->policy->import($this->warehouseStaff))->toBeFalse();
    expect($this->policy->import($this->user))->toBeFalse();
});

test('any authenticated user can view category products', function () {
    $category = Category::factory()->create();

    expect($this->policy->viewProducts($this->user, $category))->toBeTrue();
    expect($this->policy->viewProducts($this->admin, $category))->toBeTrue();
    expect($this->policy->viewProducts($this->storeManager, $category))->toBeTrue();
    expect($this->policy->viewProducts($this->warehouseStaff, $category))->toBeTrue();
});

test('only admin and store manager can manage category products', function () {
    $category = Category::factory()->create();

    expect($this->policy->manageProducts($this->admin, $category))->toBeTrue();
    expect($this->policy->manageProducts($this->storeManager, $category))->toBeTrue();
    expect($this->policy->manageProducts($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->manageProducts($this->user, $category))->toBeFalse();
});

test('only admin and store manager can assign products to category', function () {
    $category = Category::factory()->create();

    expect($this->policy->assignProducts($this->admin, $category))->toBeTrue();
    expect($this->policy->assignProducts($this->storeManager, $category))->toBeTrue();
    expect($this->policy->assignProducts($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->assignProducts($this->user, $category))->toBeFalse();
});

test('only admin can bulk update categories', function () {
    expect($this->policy->bulkUpdate($this->admin))->toBeTrue();
    expect($this->policy->bulkUpdate($this->storeManager))->toBeFalse();
    expect($this->policy->bulkUpdate($this->warehouseStaff))->toBeFalse();
    expect($this->policy->bulkUpdate($this->user))->toBeFalse();
});

test('only admin can view audit log', function () {
    expect($this->policy->viewAuditLog($this->admin))->toBeTrue();
    expect($this->policy->viewAuditLog($this->storeManager))->toBeFalse();
    expect($this->policy->viewAuditLog($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewAuditLog($this->user))->toBeFalse();
});

test('only admin and store manager can access API for writing', function () {
    expect($this->policy->accessApi($this->admin))->toBeTrue();
    expect($this->policy->accessApi($this->storeManager))->toBeTrue();
    expect($this->policy->accessApi($this->warehouseStaff))->toBeFalse();
    expect($this->policy->accessApi($this->user))->toBeFalse();
});

test('only admin and store manager can create subcategories', function () {
    $parent = Category::factory()->create(['is_active' => true]);

    expect($this->policy->createSubcategory($this->admin, $parent))->toBeTrue();
    expect($this->policy->createSubcategory($this->storeManager, $parent))->toBeTrue();
    expect($this->policy->createSubcategory($this->warehouseStaff, $parent))->toBeFalse();
    expect($this->policy->createSubcategory($this->user, $parent))->toBeFalse();
});

test('cannot create subcategory under inactive parent', function () {
    $parent = Category::factory()->create(['is_active' => false]);

    expect($this->policy->createSubcategory($this->admin, $parent))->toBeFalse();
    expect($this->policy->createSubcategory($this->storeManager, $parent))->toBeFalse();
});

test('only admin and store manager can change parent', function () {
    $category = Category::factory()->create();

    expect($this->policy->changeParent($this->admin, $category))->toBeTrue();
    expect($this->policy->changeParent($this->storeManager, $category))->toBeTrue();
    expect($this->policy->changeParent($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->changeParent($this->user, $category))->toBeFalse();
});

test('only admin and store manager can deactivate category', function () {
    $category = Category::factory()->create();

    expect($this->policy->deactivate($this->admin, $category))->toBeTrue();
    expect($this->policy->deactivate($this->storeManager, $category))->toBeTrue();
    expect($this->policy->deactivate($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->deactivate($this->user, $category))->toBeFalse();
});

test('cannot deactivate category with active products', function () {
    $category = Category::factory()->create();
    \App\Models\Product::factory()->withCategory($category)->create(['is_active' => true]);

    expect($this->policy->deactivate($this->admin, $category))->toBeFalse();
    expect($this->policy->deactivate($this->storeManager, $category))->toBeFalse();
});

test('only admin and store manager can reactivate category', function () {
    $category = Category::factory()->create();

    expect($this->policy->reactivate($this->admin, $category))->toBeTrue();
    expect($this->policy->reactivate($this->storeManager, $category))->toBeTrue();
    expect($this->policy->reactivate($this->warehouseStaff, $category))->toBeFalse();
    expect($this->policy->reactivate($this->user, $category))->toBeFalse();
});

test('only admin can view deleted categories', function () {
    expect($this->policy->viewTrashed($this->admin))->toBeTrue();
    expect($this->policy->viewTrashed($this->storeManager))->toBeFalse();
    expect($this->policy->viewTrashed($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewTrashed($this->user))->toBeFalse();
});

test('unauthenticated user cannot perform any actions', function () {
    $guest = new User();
    $category = Category::factory()->create();

    expect($this->policy->viewAny($guest))->toBeFalse();
    expect($this->policy->view($guest, $category))->toBeFalse();
    expect($this->policy->create($guest))->toBeFalse();
    expect($this->policy->update($guest, $category))->toBeFalse();
    expect($this->policy->delete($guest, $category))->toBeFalse();
});
