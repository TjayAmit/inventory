<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Create test users for each role
        $storeManager = User::factory()->create([
            'name' => 'Store Manager',
            'email' => 'manager@example.com',
        ]);
        $storeManager->assignRole('store_manager');

        $cashier = User::factory()->create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
        ]);
        $cashier->assignRole('cashier');

        $warehouseStaff = User::factory()->create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@example.com',
        ]);
        $warehouseStaff->assignRole('warehouse_staff');
    }
}
