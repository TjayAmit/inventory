<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\{
    ModelRepository,
    UserRepository,
    ProductRepository,
    BranchRepository,
    InventoryRepository,
    SupplierRepository,
    PurchaseOrderRepository,
    SalesOrderRepository,
    InventoryAdjustmentRepository,
    InventoryBatchRepository,
    PurchaseOrderItemRepository,
    SalesItemRepository,

};
use App\Repositories\Eloquent\{
    EloquentModelRepository,
    EloquentUserRepository,
    EloquentProductRepository,
    EloquentBranchRepository,
    EloquentInventoryRepository,
    EloquentSupplierRepository,
    EloquentPurchaseOrderRepository,
    EloquentSalesOrderRepository,
    EloquentInventoryAdjustmentRepository,
    EloquentInventoryBatchRepository,
    EloquentPurchaseOrderItemRepository,
    EloquentSalesItemRepository
};

use App\Models\{
    User,
    Product,
    Branch,
    Inventory,
    Supplier,
    PurchaseOrder,
    SalesOrder,
    InventoryAdjustment,
    InventoryBatch,
    PurchaseOrderItem,
    SalesItem
};

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
