<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Product Management
            'manage products',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'import products',
            
            // Category Management
            'manage categories',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Stock Management
            'manage stock',
            'view stock',
            'adjust stock',
            'view stock movements',
            'receive stock',
            
            // Sales Management
            'manage sales',
            'view sales',
            'create sales',
            'edit sales',
            'delete sales',
            'void sales',
            
            // Reports
            'view reports',
            'view sales reports',
            'view inventory reports',
            'export reports',
            
            // System Settings
            'manage system settings',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
