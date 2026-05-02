<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // User Management Routes
    Route::resource('users', UserController::class);

    // Product Management Routes - Additional routes MUST come before resource route
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/find-by-barcode', [ProductController::class, 'findByBarcode'])->name('products.find-by-barcode');
    Route::get('products/find-by-product-code', [ProductController::class, 'findByProductCode'])->name('products.find-by-product-code');
    Route::get('products/dropdown', [ProductController::class, 'dropdown'])->name('products.dropdown');
    Route::get('products/by-category/{categoryId}', [ProductController::class, 'byCategory'])->name('products.by-category');
    Route::get('products/by-brand/{brand}', [ProductController::class, 'byBrand'])->name('products.by-brand');
    Route::get('products/by-supplier/{supplier}', [ProductController::class, 'bySupplier'])->name('products.by-supplier');
    Route::get('products/with-barcodes', [ProductController::class, 'withBarcodes'])->name('products.with-barcodes');
    Route::get('products/without-barcodes', [ProductController::class, 'withoutBarcodes'])->name('products.without-barcodes');
    Route::get('products/with-profit-margins', [ProductController::class, 'withProfitMargins'])->name('products.with-profit-margins');
    Route::get('products/statistics', [ProductController::class, 'statistics'])->name('products.statistics');
    Route::put('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::put('products/{product}/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generate-barcode');
    Route::resource('products', ProductController::class);

    // Stock Management Routes - Additional routes MUST come before resource route
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::post('stocks/{product}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
    Route::get('stocks/{product}', [StockController::class, 'show'])->name('stocks.show');

    // Category Management Routes - Additional routes MUST come before resource route
    Route::get('categories/search', [CategoryController::class, 'search'])->name('categories.search');
    Route::get('categories/dropdown', [CategoryController::class, 'dropdown'])->name('categories.dropdown');
    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::get('categories/statistics', [CategoryController::class, 'statistics'])->name('categories.statistics');
    Route::get('categories/with-product-counts', [CategoryController::class, 'withProductCounts'])->name('categories.with-product-counts');
    Route::get('categories/{category}/descendants', [CategoryController::class, 'descendants'])->name('categories.descendants');
    Route::get('categories/root', [CategoryController::class, 'root'])->name('categories.root');
    Route::put('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::put('categories/sort-order', [CategoryController::class, 'updateSortOrder'])->name('categories.sort-order');
    Route::put('categories/{category}/move', [CategoryController::class, 'move'])->name('categories.move');
    Route::resource('categories', CategoryController::class);
});

require __DIR__.'/settings.php';
