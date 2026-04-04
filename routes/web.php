<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // User Management Routes
    Route::resource('users', UserController::class);

    // Product Management Routes
    Route::resource('products', ProductController::class);
    Route::put('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::put('products/{product}/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generate-barcode');

    // Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::put('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::put('categories/sort-order', [CategoryController::class, 'updateSortOrder'])->name('categories.sort-order');
    Route::put('categories/{category}/move', [CategoryController::class, 'move'])->name('categories.move');
    
    // Additional Category Routes
    Route::get('categories/search', [CategoryController::class, 'search'])->name('categories.search');
    Route::get('categories/dropdown', [CategoryController::class, 'dropdown'])->name('categories.dropdown');
    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::get('categories/statistics', [CategoryController::class, 'statistics'])->name('categories.statistics');
    Route::get('categories/with-product-counts', [CategoryController::class, 'withProductCounts'])->name('categories.with-product-counts');
    Route::get('categories/{category}/descendants', [CategoryController::class, 'descendants'])->name('categories.descendants');
    Route::get('categories/root', [CategoryController::class, 'root'])->name('categories.root');
});

require __DIR__.'/settings.php';
