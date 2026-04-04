<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserAPIController;

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

    // Future API routes can be added here
    // Example: Products, Sales, Inventory, etc.
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});
