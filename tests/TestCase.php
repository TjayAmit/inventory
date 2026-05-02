<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset cached roles and permissions
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Seed roles and permissions for tests
        $this->seedRoles();
        $this->seedPermissions();
    }

    protected function seedRoles(): void
    {
        // Create roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'store_manager']);
            Role::create(['name' => 'warehouse_staff']);
            Role::create(['name' => 'user']);
        }
    }

    protected function seedPermissions(): void
    {
        // Create permissions if they don't exist
        $permissions = [
            // Category permissions
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',
            'category.manage',
            
            // Product permissions
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.manage',
            'product.pricing',
            'product.inventory',
            'product.barcode',
            'product.statistics',
            'product.export',
            'product.import',
            'product.bulk_update',
            'product.scan',
            'product.search',
            'product.stock',
            'product.adjust_stock',
            'product.suppliers',
            'product.profit_margins',
            'product.discounts',
            'product.deactivate',
            'product.reactivate',
            'product.generate_barcode',
            'product.update_cost_price',
            'product.update_selling_price',
            'product.reorder_points',
            'product.sales_history',
            'product.view_trashed',
            'product.duplicate',
            'product.variants',
            'product.analytics',
            'product.access_api',
            
            // User permissions
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.manage',
            'user.restore',
            'user.force_delete',
            'user.view_audit_log',
            'user.access_api',
            
            // General permissions
            'dashboard.view',
            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $storeManagerRole = Role::findByName('store_manager');
        $warehouseStaffRole = Role::findByName('warehouse_staff');
        $userRole = Role::findByName('user');

        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }

        if ($storeManagerRole) {
            $storeManagerRole->givePermissionTo([
                'category.view', 'category.create', 'category.edit', 'category.manage',
                'product.view', 'product.create', 'product.edit', 'product.manage', 'product.pricing',
                'product.inventory', 'product.barcode', 'product.statistics', 'product.export',
                'product.import', 'product.bulk_update', 'product.scan', 'product.search',
                'product.stock', 'product.adjust_stock', 'product.suppliers', 'product.profit_margins',
                'product.discounts', 'product.deactivate', 'product.reactivate', 'product.generate_barcode',
                'product.update_cost_price', 'product.update_selling_price', 'product.reorder_points',
                'product.sales_history', 'product.duplicate', 'product.variants', 'product.analytics',
                'user.view', 'user.create', 'user.edit',
                'dashboard.view', 'reports.view',
            ]);
        }

        if ($warehouseStaffRole) {
            $warehouseStaffRole->givePermissionTo([
                'category.view',
                'product.view', 'product.create', 'product.edit', 'product.manage',
                'product.inventory', 'product.barcode', 'product.scan', 'product.search',
                'product.stock', 'product.adjust_stock', 'product.deactivate', 'product.reactivate',
                'product.generate_barcode', 'product.reorder_points', 'product.duplicate',
                'dashboard.view',
            ]);
        }

        if ($userRole) {
            $userRole->givePermissionTo([
                'category.view',
                'product.view', 'product.scan', 'product.search', 'product.stock',
                'dashboard.view',
            ]);
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
