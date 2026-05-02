<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\HasRolesAndPermissions;

uses(HasRolesAndPermissions::class);

it('admin user has all permissions', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('manage users'))->toBeTrue();
    expect($admin->can('view users'))->toBeTrue();
    expect($admin->can('create users'))->toBeTrue();
    expect($admin->can('edit users'))->toBeTrue();
    expect($admin->can('delete users'))->toBeTrue();
});

it('cashier user has limited permissions', function () {
    $this->setUpRolesAndPermissions();
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');

    expect($cashier->can('manage users'))->toBeFalse();
    expect($cashier->can('view users'))->toBeFalse();
    expect($cashier->can('create users'))->toBeFalse();
    expect($cashier->can('edit users'))->toBeFalse();
    expect($cashier->can('delete users'))->toBeFalse();
    expect($cashier->can('view products'))->toBeTrue();
    expect($cashier->can('create sales'))->toBeTrue();
});

it('store manager user has appropriate permissions', function () {
    $this->setUpRolesAndPermissions();
    
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');

    expect($manager->can('manage users'))->toBeFalse();
    expect($manager->can('view users'))->toBeTrue();
    expect($manager->can('create users'))->toBeTrue();
    expect($manager->can('edit users'))->toBeTrue();
    expect($manager->can('delete users'))->toBeFalse();
    expect($manager->can('manage products'))->toBeTrue();
    expect($manager->can('create sales'))->toBeTrue();
});

it('warehouse staff user has appropriate permissions', function () {
    $this->setUpRolesAndPermissions();
    
    $warehouse = User::factory()->create();
    $warehouse->assignRole('warehouse_staff');

    expect($warehouse->can('manage users'))->toBeFalse();
    expect($warehouse->can('view users'))->toBeFalse();
    expect($warehouse->can('create users'))->toBeFalse();
    expect($warehouse->can('edit users'))->toBeFalse();
    expect($warehouse->can('delete users'))->toBeFalse();
    expect($warehouse->can('manage stock'))->toBeTrue();
    expect($warehouse->can('adjust stock'))->toBeTrue();
    expect($warehouse->can('receive stock'))->toBeTrue();
});

it('user policy works correctly', function () {
    $this->setUpRolesAndPermissions();
    
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $manager = User::factory()->create();
    $manager->assignRole('store_manager');
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');

    // Test admin can do everything
    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', $cashier))->toBeTrue();
    expect($admin->can('create', User::class))->toBeTrue();
    expect($admin->can('update', $cashier))->toBeTrue();
    expect($admin->can('delete', $cashier))->toBeTrue();

    // Test manager permissions
    expect($manager->can('viewAny', User::class))->toBeTrue();
    expect($manager->can('view', $cashier))->toBeTrue();
    expect($manager->can('create', User::class))->toBeTrue();
    expect($manager->can('update', $cashier))->toBeTrue();
    expect($manager->can('delete', $cashier))->toBeTrue();

    // Test cashier cannot manage users
    expect($cashier->can('viewAny', User::class))->toBeFalse();
    expect($cashier->can('view', $manager))->toBeFalse();
    expect($cashier->can('create', User::class))->toBeFalse();
    expect($cashier->can('update', $manager))->toBeFalse();
    expect($cashier->can('delete', $manager))->toBeFalse();
});
