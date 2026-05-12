<?php

use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\PersonnelApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SalesItemApiController;
use App\Http\Controllers\Api\SalesOrderApiController;
use App\Http\Controllers\Api\SupplierApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  — /api/v1/*
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth'])->group(function () {

    // Branches
    Route::prefix('branches')->name('api.branches.')->group(function () {
        Route::get('/',        [BranchApiController::class, 'index'])->name('index');
        Route::post('/',       [BranchApiController::class, 'store'])->name('store');
        Route::get('/{branch}',    [BranchApiController::class, 'show'])->name('show');
        Route::put('/{branch}',    [BranchApiController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchApiController::class, 'destroy'])->name('destroy');
    });

    // Products
    Route::prefix('products')->name('api.products.')->group(function () {
        Route::get('/categories',  [ProductApiController::class, 'categories'])->name('categories');
        Route::get('/',            [ProductApiController::class, 'index'])->name('index');
        Route::post('/',           [ProductApiController::class, 'store'])->name('store');
        Route::get('/{product}',   [ProductApiController::class, 'show'])->name('show');
        Route::put('/{product}',   [ProductApiController::class, 'update'])->name('update');
        Route::delete('/{product}',[ProductApiController::class, 'destroy'])->name('destroy');
    });

    // Inventory
    Route::prefix('inventory')->name('api.inventory.')->group(function () {
        Route::get('/products',        [InventoryApiController::class, 'products'])->name('products');
        Route::get('/branches',        [InventoryApiController::class, 'branches'])->name('branches');
        Route::get('/',                [InventoryApiController::class, 'index'])->name('index');
        Route::post('/',               [InventoryApiController::class, 'store'])->name('store');
        Route::get('/{inventory}',     [InventoryApiController::class, 'show'])->name('show');
        Route::put('/{inventory}',     [InventoryApiController::class, 'update'])->name('update');
        Route::delete('/{inventory}',  [InventoryApiController::class, 'destroy'])->name('destroy');
    });

    // Suppliers
    Route::prefix('suppliers')->name('api.suppliers.')->group(function () {
        Route::get('/',            [SupplierApiController::class, 'index'])->name('index');
        Route::post('/',           [SupplierApiController::class, 'store'])->name('store');
        Route::get('/{supplier}',  [SupplierApiController::class, 'show'])->name('show');
        Route::put('/{supplier}',  [SupplierApiController::class, 'update'])->name('update');
        Route::delete('/{supplier}',[SupplierApiController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->name('api.users.')->group(function () {
        Route::get('/',        [UserApiController::class, 'index'])->name('index');
        Route::post('/',       [UserApiController::class, 'store'])->name('store');
        Route::get('/{user}',  [UserApiController::class, 'show'])->name('show');
        Route::put('/{user}',  [UserApiController::class, 'update'])->name('update');
        Route::delete('/{user}',[UserApiController::class, 'destroy'])->name('destroy');
    });

    // Sales Orders
    Route::prefix('sales-orders')->name('api.sales-orders.')->group(function () {
        Route::get('/',              [SalesOrderApiController::class, 'index'])->name('index');
        Route::post('/',             [SalesOrderApiController::class, 'store'])->name('store');
        Route::get('/{salesOrder}',  [SalesOrderApiController::class, 'show'])->name('show');
        Route::put('/{salesOrder}',  [SalesOrderApiController::class, 'update'])->name('update');
        Route::delete('/{salesOrder}',[SalesOrderApiController::class, 'destroy'])->name('destroy');
    });

    // Sales Items
    Route::prefix('sales-items')->name('api.sales-items.')->group(function () {
        Route::get('/',            [SalesItemApiController::class, 'index'])->name('index');
        Route::post('/',           [SalesItemApiController::class, 'store'])->name('store');
        Route::get('/{salesItem}', [SalesItemApiController::class, 'show'])->name('show');
        Route::put('/{salesItem}', [SalesItemApiController::class, 'update'])->name('update');
        Route::delete('/{salesItem}',[SalesItemApiController::class, 'destroy'])->name('destroy');
    });

    // Invoices / POS
    Route::prefix('invoices')->name('api.invoices.')->group(function () {
        Route::get('/',                                          [InvoiceApiController::class, 'index'])->name('index');
        Route::post('/',                                         [InvoiceApiController::class, 'store'])->name('store');
        Route::get('/{invoice}',                                 [InvoiceApiController::class, 'show'])->name('show');
        Route::post('/{invoice}/items',                          [InvoiceApiController::class, 'addItem'])->name('add-item');
        Route::put('/{invoice}/items/{salesItem}',               [InvoiceApiController::class, 'updateItem'])->name('update-item');
        Route::delete('/{invoice}/items/{salesItem}',            [InvoiceApiController::class, 'removeItem'])->name('remove-item');
        Route::post('/{invoice}/checkout',                       [InvoiceApiController::class, 'checkout'])->name('checkout');
        Route::delete('/{invoice}',                              [InvoiceApiController::class, 'destroy'])->name('destroy');
    });

    // Personnel
    Route::prefix('personnel')->name('api.personnel.')->group(function () {
        Route::get('/branches',                [PersonnelApiController::class, 'branches'])->name('branches');
        Route::get('/',                        [PersonnelApiController::class, 'index'])->name('index');
        Route::post('/',                       [PersonnelApiController::class, 'store'])->name('store');
        Route::get('/{user}',                  [PersonnelApiController::class, 'show'])->name('show');
        Route::put('/{user}',                  [PersonnelApiController::class, 'update'])->name('update');
        Route::delete('/{user}',               [PersonnelApiController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/assign-branch',   [PersonnelApiController::class, 'assignBranch'])->name('assign-branch');
        Route::delete('/{user}/revoke-branch', [PersonnelApiController::class, 'revokeBranch'])->name('revoke-branch');
        Route::post('/{user}/assign-role',     [PersonnelApiController::class, 'assignRole'])->name('assign-role');
        Route::delete('/{user}/revoke-role',   [PersonnelApiController::class, 'revokeRole'])->name('revoke-role');
    });

});

// Health check (unauthenticated)
Route::get('/health', fn () => response()->json([
    'status'    => 'ok',
    'timestamp' => now()->toISOString(),
    'version'   => '1.0.0',
]));
