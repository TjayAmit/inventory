<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesItemController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('branches', BranchController::class);

    // Product Routes
    Route::resource('products', ProductController::class);

    // Inventory Routes
    Route::resource('inventory', InventoryController::class);

    // Supplier Routes
    Route::resource('suppliers', SupplierController::class);

    // User Routes
    Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Sales Order Routes
    Route::resource('sales-orders', SalesOrderController::class);

    // Sales Item Routes
    Route::resource('sales-items', SalesItemController::class);

    // Invoice / POS Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{salesOrder}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{salesOrder}/items', [InvoiceController::class, 'addItem'])->name('add-item');
        Route::put('/{salesOrder}/items/{salesItem}', [InvoiceController::class, 'updateItem'])->name('update-item');
        Route::delete('/{salesOrder}/items/{salesItem}', [InvoiceController::class, 'removeItem'])->name('remove-item');
        Route::post('/{salesOrder}/checkout', [InvoiceController::class, 'checkout'])->name('checkout');
        Route::delete('/{salesOrder}', [InvoiceController::class, 'destroy'])->name('destroy');
    });

    // Transaction Routes (read-only history)
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Personnel Routes
    Route::prefix('personnel')->name('personnel.')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('index');
        Route::get('/create', [PersonnelController::class, 'create'])->name('create');
        Route::post('/', [PersonnelController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [PersonnelController::class, 'edit'])->name('edit');
        Route::put('/{user}', [PersonnelController::class, 'update'])->name('update');
        Route::delete('/{user}', [PersonnelController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/assign-branch', [PersonnelController::class, 'assignBranch'])->name('assign-branch');
        Route::delete('/{user}/revoke-branch', [PersonnelController::class, 'revokeBranch'])->name('revoke-branch');
        Route::post('/{user}/assign-role', [PersonnelController::class, 'assignRole'])->name('assign-role');
        Route::delete('/{user}/revoke-role', [PersonnelController::class, 'revokeRole'])->name('revoke-role');
    });
});

require __DIR__.'/settings.php';
