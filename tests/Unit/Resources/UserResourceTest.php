<?php

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $this->resource = new UserResource($this->user);
});

it('transforms user to array with basic fields', function () {
    $request = new Request();
    $result = $this->resource->toArray($request);

    expect($result)->toHaveKey('id');
    expect($result)->toHaveKey('name');
    expect($result)->toHaveKey('email');
    expect($result)->toHaveKey('created_at');
    expect($result)->toHaveKey('updated_at');
    expect($result['id'])->toBe($this->user->id);
    expect($result['name'])->toBe($this->user->name);
    expect($result['email'])->toBe($this->user->email);
});

it('includes conditional fields when relationships are loaded', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $roles = collect([
        (object)['name' => 'store_manager'],
        (object)['name' => 'warehouse_staff'],
    ]);
    $user->setRelation('roles', $roles);
    
    $resource = new UserResource($user);
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result)->toHaveKey('roles');
    expect($result)->toHaveKey('formatted_roles');
    expect($result)->toHaveKey('is_admin');
    expect($result['roles'])->not->toBeNull();
    expect($result['formatted_roles'])->not->toBeNull();
    expect($result['is_admin'])->toBeBool();
});

it('includes permissions when relationship is loaded', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $permissions = collect([
        (object)['name' => 'manage users'],
        (object)['name' => 'view reports'],
    ]);
    $user->setRelation('permissions', $permissions);
    
    $resource = new UserResource($user);
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result)->toHaveKey('permissions');
    expect($result['permissions'])->not->toBeNull();
});

it('includes email_verified_at when not null', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'email_verified_at' => now()]);
    $resource = new UserResource($user);
    
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result)->toHaveKey('email_verified_at');
    expect($result['email_verified_at'])->not->toBeNull();
});

it('correctly identifies admin users', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $roles = collect([
        (object)['name' => 'admin'],
        (object)['name' => 'store_manager'],
    ]);
    $user->setRelation('roles', $roles);
    
    $resource = new UserResource($user);
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result['is_admin'])->toBeTrue();
});

it('correctly identifies non-admin users', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $roles = collect([
        (object)['name' => 'store_manager'],
        (object)['name' => 'cashier'],
    ]);
    $user->setRelation('roles', $roles);
    
    $resource = new UserResource($user);
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result['is_admin'])->toBeFalse();
});

it('includes additional metadata in with method', function () {
    $request = new Request();
    $result = $this->resource->with($request);

    expect($result)->toHaveKey('meta');
    expect($result['meta'])->toHaveKey('version');
    expect($result['meta'])->toHaveKey('timestamp');
    expect($result['meta']['version'])->toBe('1.0');
});

it('handles user with no roles correctly', function () {
    $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
    $user->setRelation('roles', collect());
    
    $resource = new UserResource($user);
    $request = new Request();
    $result = $resource->toArray($request);

    expect($result['is_admin'])->toBeFalse();
});
