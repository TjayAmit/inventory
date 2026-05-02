<?php

namespace Tests\Concerns;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

trait HasRolesAndPermissions
{
    protected function setUpRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
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
            'delete sales',
            'void sales',
            'view reports',
            'view sales reports',
            'view inventory reports',
            'export reports',
            'manage system settings',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $roles = ['admin', 'store_manager', 'cashier', 'warehouse_staff', 'user'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        $storeManagerRole = Role::findByName('store_manager');
        $storeManagerRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
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

        $cashierRole = Role::findByName('cashier');
        $cashierRole->givePermissionTo([
            'view products',
            'view stock',
            'create sales',
            'view sales',
            'view dashboard',
        ]);

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
