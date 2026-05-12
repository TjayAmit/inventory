<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Default main branch
        $branch = Branch::create([
            'code'           => 'HQ-001',
            'name'           => 'Main Branch',
            'address'        => '123 Main Street',
            'city'           => 'Manila',
            'phone'          => '+63 2 8123 4567',
            'email'          => 'main@store.com',
            'is_active'      => true,
            'is_main_branch' => true,
            'timezone'       => 'Asia/Manila',
            'currency'       => 'PHP',
            'tax_rate'       => 0.12,
        ]);

        // Admin — system-level access, not tied to a branch
        $admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Owner — business owner, assigned to main branch
        $owner = User::factory()->create([
            'name'      => 'Store Owner',
            'email'     => 'owner@example.com',
            'branch_id' => $branch->id,
        ]);
        $owner->assignRole('owner');
        $branch->update(['manager_id' => $owner->id]);

        // Staff (store manager) — assigned to main branch
        $staff = User::factory()->create([
            'name'      => 'Store Manager',
            'email'     => 'manager@example.com',
            'branch_id' => $branch->id,
        ]);
        $staff->assignRole('store_manager');

        // Cashier — assigned to main branch
        $cashier = User::factory()->create([
            'name'      => 'Cashier User',
            'email'     => 'cashier@example.com',
            'branch_id' => $branch->id,
        ]);
        $cashier->assignRole('cashier');
    }
}
