<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\{
    UserService,
    ProductService,
    BranchService,
    InventoryService,
    SupplierService,
    PurchaseOrderService,
    SalesOrderService,
    InventoryAdjustmentService,
    InventoryBatchService,
    PurchaseOrderItemService,
    SalesItemService
};

class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // User
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService($app->make(\App\Repositories\Interfaces\UserRepository::class));
        });

        // Product
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService($app->make(\App\Repositories\Interfaces\ProductRepository::class));
        });

        // Branch
        $this->app->singleton(BranchService::class, function ($app) {
            return new BranchService($app->make(\App\Repositories\Interfaces\BranchRepository::class));
        });

        // Inventory
        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService($app->make(\App\Repositories\Interfaces\InventoryRepository::class));
        });

        // Supplier
        $this->app->singleton(SupplierService::class, function ($app) {
            return new SupplierService($app->make(\App\Repositories\Interfaces\SupplierRepository::class));
        });

        // Purchase Order
        $this->app->singleton(PurchaseOrderService::class, function ($app) {
            return new PurchaseOrderService($app->make(\App\Repositories\Interfaces\PurchaseOrderRepository::class));
        });

        // Sales Order
        $this->app->singleton(SalesOrderService::class, function ($app) {
            return new SalesOrderService($app->make(\App\Repositories\Interfaces\SalesOrderRepository::class));
        });

        // Inventory Adjustment
        $this->app->singleton(InventoryAdjustmentService::class, function ($app) {
            return new InventoryAdjustmentService($app->make(\App\Repositories\Interfaces\InventoryAdjustmentRepository::class));
        });

        // Inventory Batch
        $this->app->singleton(InventoryBatchService::class, function ($app) {
            return new InventoryBatchService($app->make(\App\Repositories\Interfaces\InventoryBatchRepository::class));
        });

        // Purchase Order Item
        $this->app->singleton(PurchaseOrderItemService::class, function ($app) {
            return new PurchaseOrderItemService($app->make(\App\Repositories\Interfaces\PurchaseOrderItemRepository::class));
        });

        // Sales Item
        $this->app->singleton(SalesItemService::class, function ($app) {
            return new SalesItemService($app->make(\App\Repositories\Interfaces\SalesItemRepository::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
