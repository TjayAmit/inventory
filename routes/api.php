<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\CategoryAPIController;
use App\Http\Controllers\API\ProductAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware(['auth'])->group(function () {
    
    // User Management API Routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserAPIController::class, 'index'])->name('api.users.index');
        Route::post('/', [UserAPIController::class, 'store'])->name('api.users.store');
        Route::get('/search', [UserAPIController::class, 'search'])->name('api.users.search');
        Route::get('/statistics', [UserAPIController::class, 'statistics'])->name('api.users.statistics');
        Route::get('/{user}', [UserAPIController::class, 'show'])->name('api.users.show');
        Route::put('/{user}', [UserAPIController::class, 'update'])->name('api.users.update');
        Route::delete('/{user}', [UserAPIController::class, 'destroy'])->name('api.users.destroy');
    });

    // Category Management API Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryAPIController::class, 'index'])->name('api.categories.index');
        Route::post('/', [CategoryAPIController::class, 'store'])->name('api.categories.store');
        Route::get('/active', [CategoryAPIController::class, 'active'])->name('api.categories.active');
        Route::get('/tree', [CategoryAPIController::class, 'tree'])->name('api.categories.tree');
        Route::get('/root', [CategoryAPIController::class, 'root'])->name('api.categories.root');
        Route::get('/search', [CategoryAPIController::class, 'search'])->name('api.categories.search');
        Route::get('/dropdown', [CategoryAPIController::class, 'dropdown'])->name('api.categories.dropdown');
        Route::get('/statistics', [CategoryAPIController::class, 'statistics'])->name('api.categories.statistics');
        Route::get('/with-product-counts', [CategoryAPIController::class, 'withProductCounts'])->name('api.categories.with-product-counts');
        Route::put('/sort-order', [CategoryAPIController::class, 'updateSortOrder'])->name('api.categories.sort-order');
        Route::get('/{category}/children', [CategoryAPIController::class, 'children'])->name('api.categories.children');
        Route::get('/{category}/descendants', [CategoryAPIController::class, 'descendants'])->name('api.categories.descendants');
        Route::put('/{category}/toggle-status', [CategoryAPIController::class, 'toggleStatus'])->name('api.categories.toggle-status');
        Route::put('/{category}/move', [CategoryAPIController::class, 'move'])->name('api.categories.move');
        Route::get('/{category}', [CategoryAPIController::class, 'show'])->name('api.categories.show');
        Route::put('/{category}', [CategoryAPIController::class, 'update'])->name('api.categories.update');
        Route::delete('/{category}', [CategoryAPIController::class, 'destroy'])->name('api.categories.destroy');
    });

    // Product Management API Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductAPIController::class, 'index'])->name('api.products.index');
        Route::post('/', [ProductAPIController::class, 'store'])->name('api.products.store');
        Route::get('/active', [ProductAPIController::class, 'active'])->name('api.products.active');
        Route::get('/search', [ProductAPIController::class, 'search'])->name('api.products.search');
        Route::get('/find-by-barcode', [ProductAPIController::class, 'findByBarcode'])->name('api.products.find-by-barcode');
        Route::get('/find-by-product-code', [ProductAPIController::class, 'findByProductCode'])->name('api.products.find-by-product-code');
        Route::get('/dropdown', [ProductAPIController::class, 'dropdown'])->name('api.products.dropdown');
        Route::get('/statistics', [ProductAPIController::class, 'statistics'])->name('api.products.statistics');
        Route::get('/with-barcodes', [ProductAPIController::class, 'withBarcodes'])->name('api.products.with-barcodes');
        Route::get('/without-barcodes', [ProductAPIController::class, 'withoutBarcodes'])->name('api.products.without-barcodes');
        Route::get('/with-profit-margins', [ProductAPIController::class, 'withProfitMargins'])->name('api.products.with-profit-margins');
        Route::put('/bulk-update', [ProductAPIController::class, 'bulkUpdate'])->name('api.products.bulk-update');
        Route::post('/import', [ProductAPIController::class, 'import'])->name('api.products.import');
        Route::get('/by-price-range', [ProductAPIController::class, 'byPriceRange'])->name('api.products.by-price-range');
        Route::get('/by-category/{categoryId}', [ProductAPIController::class, 'byCategory'])->name('api.products.by-category');
        Route::get('/by-brand/{brand}', [ProductAPIController::class, 'byBrand'])->name('api.products.by-brand');
        Route::get('/by-supplier/{supplier}', [ProductAPIController::class, 'bySupplier'])->name('api.products.by-supplier');
        Route::put('/{product}/toggle-status', [ProductAPIController::class, 'toggleStatus'])->name('api.products.toggle-status');
        Route::put('/{product}/generate-barcode', [ProductAPIController::class, 'generateBarcode'])->name('api.products.generate-barcode');
        Route::get('/{product}', [ProductAPIController::class, 'show'])->name('api.products.show');
        Route::put('/{product}', [ProductAPIController::class, 'update'])->name('api.products.update');
        Route::delete('/{product}', [ProductAPIController::class, 'destroy'])->name('api.products.destroy');
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});
