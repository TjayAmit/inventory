<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\ModelRepository;
use App\Repositories\Eloquent\EloquentModelRepository;
use App\Repositories\Interfaces\UserRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Interfaces\ProductRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Interfaces\BranchRepository;
use App\Repositories\Eloquent\EloquentBranchRepository;
use App\Repositories\Interfaces\InventoryRepository;
use App\Repositories\Eloquent\EloquentInventoryRepository;
use App\Repositories\Interfaces\SupplierRepository;
use App\Repositories\Eloquent\EloquentSupplierRepository;
use App\Repositories\Interfaces\PurchaseOrderRepository;
use App\Repositories\Eloquent\EloquentPurchaseOrderRepository;
use App\Repositories\Interfaces\SalesOrderRepository;
use App\Repositories\Eloquent\EloquentSalesOrderRepository;
use App\Repositories\Interfaces\InventoryAdjustmentRepository;
use App\Repositories\Eloquent\EloquentInventoryAdjustmentRepository;
use App\Repositories\Interfaces\InventoryBatchRepository;
use App\Repositories\Eloquent\EloquentInventoryBatchRepository;
use App\Repositories\Interfaces\PurchaseOrderItemRepository;
use App\Repositories\Eloquent\EloquentPurchaseOrderItemRepository;
use App\Repositories\Interfaces\SalesItemRepository;
use App\Repositories\Eloquent\EloquentSalesItemRepository;
use App\Models\User;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\InventoryAdjustment;
use App\Models\InventoryBatch;
use App\Models\PurchaseOrderItem;
use App\Models\SalesItem;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Base repository
        $this->app->bind(ModelRepository::class, EloquentModelRepository::class);

        // User
        $this->app->bind(UserRepository::class, function ($app) {
            return new EloquentUserRepository(new User());
        });

        // Product
        $this->app->bind(ProductRepository::class, function ($app) {
            return new EloquentProductRepository(new Product());
        });

        // Branch
        $this->app->bind(BranchRepository::class, function ($app) {
            return new EloquentBranchRepository(new Branch());
        });

        // Inventory
        $this->app->bind(InventoryRepository::class, function ($app) {
            return new EloquentInventoryRepository(new Inventory());
        });

        // Supplier
        $this->app->bind(SupplierRepository::class, function ($app) {
            return new EloquentSupplierRepository(new Supplier());
        });

        // Purchase Order
        $this->app->bind(PurchaseOrderRepository::class, function ($app) {
            return new EloquentPurchaseOrderRepository(new PurchaseOrder());
        });

        // Sales Order
        $this->app->bind(SalesOrderRepository::class, function ($app) {
            return new EloquentSalesOrderRepository(new SalesOrder());
        });

        // Inventory Adjustment
        $this->app->bind(InventoryAdjustmentRepository::class, function ($app) {
            return new EloquentInventoryAdjustmentRepository(new InventoryAdjustment());
        });

        // Inventory Batch
        $this->app->bind(InventoryBatchRepository::class, function ($app) {
            return new EloquentInventoryBatchRepository(new InventoryBatch());
        });

        // Purchase Order Item
        $this->app->bind(PurchaseOrderItemRepository::class, function ($app) {
            return new EloquentPurchaseOrderItemRepository(new PurchaseOrderItem());
        });

        // Sales Item
        $this->app->bind(SalesItemRepository::class, function ($app) {
            return new EloquentSalesItemRepository(new SalesItem());
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
