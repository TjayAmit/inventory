<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin gets all permissions
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Store Manager permissions
        $storeManagerRole = Role::findByName('store_manager');
        $storeManagerRole->givePermissionTo([
            'manage products',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'import products',
            'manage categories',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'manage stock',
            'view stock',
            'adjust stock',
            'view stock movements',
            'receive stock',
            'manage sales',
            'view sales',
            'create sales',
            'edit sales',
            'void sales',
            'view reports',
            'view sales reports',
            'view inventory reports',
            'export reports',
            'view dashboard',
        ]);

        // Cashier permissions
        $cashierRole = Role::findByName('cashier');
        $cashierRole->givePermissionTo([
            'view products',
            'view stock',
            'create sales',
            'view sales',
            'view dashboard',
        ]);

        // Warehouse Staff permissions
        $warehouseRole = Role::findByName('warehouse_staff');
        $warehouseRole->givePermissionTo([
            'view products',
            'manage stock',
            'view stock',
            'adjust stock',
            'view stock movements',
            'receive stock',
            'view dashboard',
        ]);
    }
}
