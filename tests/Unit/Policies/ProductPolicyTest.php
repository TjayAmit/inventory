<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ProductPolicy();
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->storeManager = User::factory()->create()->assignRole('store_manager');
    $this->warehouseStaff = User::factory()->create()->assignRole('warehouse_staff');
    $this->user = User::factory()->create()->assignRole('user');
});

test('any authenticated user can view any products', function () {
    expect($this->policy->viewAny($this->user))->toBeTrue();
    expect($this->policy->viewAny($this->admin))->toBeTrue();
    expect($this->policy->viewAny($this->storeManager))->toBeTrue();
    expect($this->policy->viewAny($this->warehouseStaff))->toBeTrue();
});

test('any authenticated user can view product', function () {
    $product = Product::factory()->create();

    expect($this->policy->view($this->user, $product))->toBeTrue();
    expect($this->policy->view($this->admin, $product))->toBeTrue();
    expect($this->policy->view($this->storeManager, $product))->toBeTrue();
    expect($this->policy->view($this->warehouseStaff, $product))->toBeTrue();
});

test('only admin, store manager, and warehouse staff can create products', function () {
    expect($this->policy->create($this->admin))->toBeTrue();
    expect($this->policy->create($this->storeManager))->toBeTrue();
    expect($this->policy->create($this->warehouseStaff))->toBeTrue();
    expect($this->policy->create($this->user))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can update products', function () {
    $product = Product::factory()->create();

    expect($this->policy->update($this->admin, $product))->toBeTrue();
    expect($this->policy->update($this->storeManager, $product))->toBeTrue();
    expect($this->policy->update($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->update($this->user, $product))->toBeFalse();
});

test('only admin can delete products', function () {
    $product = Product::factory()->create();

    expect($this->policy->delete($this->admin, $product))->toBeTrue();
    expect($this->policy->delete($this->storeManager, $product))->toBeFalse();
    expect($this->policy->delete($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->delete($this->user, $product))->toBeFalse();
});

// Note: Test for 'admin cannot delete product with sales' removed because SaleItem model doesn't exist
// This test can be re-added when the sales module is implemented

test('only admin can restore products', function () {
    $product = Product::factory()->create();

    expect($this->policy->restore($this->admin, $product))->toBeTrue();
    expect($this->policy->restore($this->storeManager, $product))->toBeFalse();
    expect($this->policy->restore($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->restore($this->user, $product))->toBeFalse();
});

test('only admin can force delete products', function () {
    $product = Product::factory()->create();

    expect($this->policy->forceDelete($this->admin, $product))->toBeTrue();
    expect($this->policy->forceDelete($this->storeManager, $product))->toBeFalse();
    expect($this->policy->forceDelete($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->forceDelete($this->user, $product))->toBeFalse();
});

test('only admin and store manager can manage pricing', function () {
    $product = Product::factory()->create();

    expect($this->policy->managePricing($this->admin, $product))->toBeTrue();
    expect($this->policy->managePricing($this->storeManager, $product))->toBeTrue();
    expect($this->policy->managePricing($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->managePricing($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can toggle status', function () {
    $product = Product::factory()->create();

    expect($this->policy->toggleStatus($this->admin, $product))->toBeTrue();
    expect($this->policy->toggleStatus($this->storeManager, $product))->toBeTrue();
    expect($this->policy->toggleStatus($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->toggleStatus($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can update inventory', function () {
    $product = Product::factory()->create();

    expect($this->policy->updateInventory($this->admin, $product))->toBeTrue();
    expect($this->policy->updateInventory($this->storeManager, $product))->toBeTrue();
    expect($this->policy->updateInventory($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->updateInventory($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can manage barcode', function () {
    $product = Product::factory()->create();

    expect($this->policy->manageBarcode($this->admin, $product))->toBeTrue();
    expect($this->policy->manageBarcode($this->storeManager, $product))->toBeTrue();
    expect($this->policy->manageBarcode($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->manageBarcode($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can assign category', function () {
    $product = Product::factory()->create();

    expect($this->policy->assignCategory($this->admin, $product))->toBeTrue();
    expect($this->policy->assignCategory($this->storeManager, $product))->toBeTrue();
    expect($this->policy->assignCategory($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->assignCategory($this->user, $product))->toBeFalse();
});

test('only admin and store manager can view statistics', function () {
    expect($this->policy->viewStatistics($this->admin))->toBeTrue();
    expect($this->policy->viewStatistics($this->storeManager))->toBeTrue();
    expect($this->policy->viewStatistics($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewStatistics($this->user))->toBeFalse();
});

test('only admin and store manager can export products', function () {
    expect($this->policy->export($this->admin))->toBeTrue();
    expect($this->policy->export($this->storeManager))->toBeTrue();
    expect($this->policy->export($this->warehouseStaff))->toBeFalse();
    expect($this->policy->export($this->user))->toBeFalse();
});

test('only admin and store manager can import products', function () {
    expect($this->policy->import($this->admin))->toBeTrue();
    expect($this->policy->import($this->storeManager))->toBeTrue();
    expect($this->policy->import($this->warehouseStaff))->toBeFalse();
    expect($this->policy->import($this->user))->toBeFalse();
});

test('only admin and store manager can bulk update products', function () {
    expect($this->policy->bulkUpdate($this->admin))->toBeTrue();
    expect($this->policy->bulkUpdate($this->storeManager))->toBeTrue();
    expect($this->policy->bulkUpdate($this->warehouseStaff))->toBeFalse();
    expect($this->policy->bulkUpdate($this->user))->toBeFalse();
});

test('only admin can view audit log', function () {
    expect($this->policy->viewAuditLog($this->admin))->toBeTrue();
    expect($this->policy->viewAuditLog($this->storeManager))->toBeFalse();
    expect($this->policy->viewAuditLog($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewAuditLog($this->user))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can access API for writing', function () {
    expect($this->policy->accessApi($this->admin))->toBeTrue();
    expect($this->policy->accessApi($this->storeManager))->toBeTrue();
    expect($this->policy->accessApi($this->warehouseStaff))->toBeTrue();
    expect($this->policy->accessApi($this->user))->toBeFalse();
});

test('any authenticated user can scan barcode', function () {
    $product = Product::factory()->create();

    expect($this->policy->scanBarcode($this->user))->toBeTrue();
    expect($this->policy->scanBarcode($this->admin))->toBeTrue();
    expect($this->policy->scanBarcode($this->storeManager))->toBeTrue();
    expect($this->policy->scanBarcode($this->warehouseStaff))->toBeTrue();
});

test('any authenticated user can search products', function () {
    expect($this->policy->search($this->user))->toBeTrue();
    expect($this->policy->search($this->admin))->toBeTrue();
    expect($this->policy->search($this->storeManager))->toBeTrue();
    expect($this->policy->search($this->warehouseStaff))->toBeTrue();
});

test('any authenticated user can view stock levels', function () {
    $product = Product::factory()->create();

    expect($this->policy->viewStock($this->user, $product))->toBeTrue();
    expect($this->policy->viewStock($this->admin, $product))->toBeTrue();
    expect($this->policy->viewStock($this->storeManager, $product))->toBeTrue();
    expect($this->policy->viewStock($this->warehouseStaff, $product))->toBeTrue();
});

test('only admin, store manager, and warehouse staff can adjust stock', function () {
    $product = Product::factory()->create();

    expect($this->policy->adjustStock($this->admin, $product))->toBeTrue();
    expect($this->policy->adjustStock($this->storeManager, $product))->toBeTrue();
    expect($this->policy->adjustStock($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->adjustStock($this->user, $product))->toBeFalse();
});

test('only admin and store manager can manage suppliers', function () {
    $product = Product::factory()->create();

    expect($this->policy->manageSuppliers($this->admin, $product))->toBeTrue();
    expect($this->policy->manageSuppliers($this->storeManager, $product))->toBeTrue();
    expect($this->policy->manageSuppliers($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->manageSuppliers($this->user, $product))->toBeFalse();
});

test('only admin and store manager can view profit margins', function () {
    expect($this->policy->viewProfitMargins($this->admin))->toBeTrue();
    expect($this->policy->viewProfitMargins($this->storeManager))->toBeTrue();
    expect($this->policy->viewProfitMargins($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewProfitMargins($this->user))->toBeFalse();
});

test('only admin and store manager can manage discounts', function () {
    $product = Product::factory()->create();

    expect($this->policy->manageDiscounts($this->admin, $product))->toBeTrue();
    expect($this->policy->manageDiscounts($this->storeManager, $product))->toBeTrue();
    expect($this->policy->manageDiscounts($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->manageDiscounts($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can deactivate product', function () {
    $product = Product::factory()->create();

    expect($this->policy->deactivate($this->admin, $product))->toBeTrue();
    expect($this->policy->deactivate($this->storeManager, $product))->toBeTrue();
    expect($this->policy->deactivate($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->deactivate($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can reactivate product', function () {
    $product = Product::factory()->create();

    expect($this->policy->reactivate($this->admin, $product))->toBeTrue();
    expect($this->policy->reactivate($this->storeManager, $product))->toBeTrue();
    expect($this->policy->reactivate($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->reactivate($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can generate barcode', function () {
    $product = Product::factory()->create();

    expect($this->policy->generateBarcode($this->admin, $product))->toBeTrue();
    expect($this->policy->generateBarcode($this->storeManager, $product))->toBeTrue();
    expect($this->policy->generateBarcode($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->generateBarcode($this->user, $product))->toBeFalse();
});

test('only admin and store manager can update cost price', function () {
    $product = Product::factory()->create();

    expect($this->policy->updateCostPrice($this->admin, $product))->toBeTrue();
    expect($this->policy->updateCostPrice($this->storeManager, $product))->toBeTrue();
    expect($this->policy->updateCostPrice($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->updateCostPrice($this->user, $product))->toBeFalse();
});

test('only admin and store manager can update selling price', function () {
    $product = Product::factory()->create();

    expect($this->policy->updateSellingPrice($this->admin, $product))->toBeTrue();
    expect($this->policy->updateSellingPrice($this->storeManager, $product))->toBeTrue();
    expect($this->policy->updateSellingPrice($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->updateSellingPrice($this->user, $product))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can manage reorder points', function () {
    $product = Product::factory()->create();

    expect($this->policy->manageReorderPoints($this->admin, $product))->toBeTrue();
    expect($this->policy->manageReorderPoints($this->storeManager, $product))->toBeTrue();
    expect($this->policy->manageReorderPoints($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->manageReorderPoints($this->user, $product))->toBeFalse();
});

test('only admin and store manager can view sales history', function () {
    $product = Product::factory()->create();

    expect($this->policy->viewSalesHistory($this->admin, $product))->toBeTrue();
    expect($this->policy->viewSalesHistory($this->storeManager, $product))->toBeTrue();
    expect($this->policy->viewSalesHistory($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->viewSalesHistory($this->user, $product))->toBeFalse();
});

test('only admin can view deleted products', function () {
    expect($this->policy->viewTrashed($this->admin))->toBeTrue();
    expect($this->policy->viewTrashed($this->storeManager))->toBeFalse();
    expect($this->policy->viewTrashed($this->warehouseStaff))->toBeFalse();
    expect($this->policy->viewTrashed($this->user))->toBeFalse();
});

test('only admin, store manager, and warehouse staff can duplicate product', function () {
    $product = Product::factory()->create();

    expect($this->policy->duplicate($this->admin, $product))->toBeTrue();
    expect($this->policy->duplicate($this->storeManager, $product))->toBeTrue();
    expect($this->policy->duplicate($this->warehouseStaff, $product))->toBeTrue();
    expect($this->policy->duplicate($this->user, $product))->toBeFalse();
});

test('only admin and store manager can manage variants', function () {
    $product = Product::factory()->create();

    expect($this->policy->manageVariants($this->admin, $product))->toBeTrue();
    expect($this->policy->manageVariants($this->storeManager, $product))->toBeTrue();
    expect($this->policy->manageVariants($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->manageVariants($this->user, $product))->toBeFalse();
});

test('only admin and store manager can view analytics', function () {
    $product = Product::factory()->create();

    expect($this->policy->viewAnalytics($this->admin, $product))->toBeTrue();
    expect($this->policy->viewAnalytics($this->storeManager, $product))->toBeTrue();
    expect($this->policy->viewAnalytics($this->warehouseStaff, $product))->toBeFalse();
    expect($this->policy->viewAnalytics($this->user, $product))->toBeFalse();
});

test('unauthenticated user cannot perform any actions', function () {
    $guest = new User();
    $product = Product::factory()->create();

    expect($this->policy->viewAny($guest))->toBeFalse();
    expect($this->policy->view($guest, $product))->toBeFalse();
    expect($this->policy->create($guest))->toBeFalse();
    expect($this->policy->update($guest, $product))->toBeFalse();
    expect($this->policy->delete($guest, $product))->toBeFalse();
});
