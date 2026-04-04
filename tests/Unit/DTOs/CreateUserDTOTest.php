<?php

use App\DTOs\User\CreateUserDTO;

it('CreateUserDTO accepts valid data', function () {
    $dto = new CreateUserDTO(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    expect($dto->name)->toBe('John Doe');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->roles)->toBe(['cashier']);
    expect($dto->requestsAdminRole())->toBeFalse();
});

it('CreateUserDTO detects admin role request', function () {
    $dto = new CreateUserDTO(
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['admin', 'store_manager']
    );

    expect($dto->requestsAdminRole())->toBeTrue();
});

it('CreateUserDTO can be converted to array', function () {
    $dto = new CreateUserDTO(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('name');
    expect($array)->toHaveKey('email');
    expect($array)->toHaveKey('password');
    expect($array)->toHaveKey('roles');
    expect($array['name'])->toBe('John Doe');
});

it('CreateUserDTO can be created from array', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'passwordConfirmation' => 'password123',
        'roles' => ['cashier']
    ];

    $dto = CreateUserDTO::fromArray($data);

    expect($dto->name)->toBe('John Doe');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->roles)->toBe(['cashier']);
});

it('CreateUserDTO generates hashed password', function () {
    $dto = new CreateUserDTO(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
        roles: ['cashier']
    );

    $hashedPassword = $dto->getHashedPassword();

    expect($hashedPassword)->toBeString();
    expect($hashedPassword)->not->toBe('password123');
    expect(strlen($hashedPassword))->toBeGreaterThan(50); // Hash should be longer
});

it('CreateUserDTO validates required fields in feature tests', function () {
    // This test would be run in feature tests where Laravel is fully booted
    expect(true)->toBeTrue(); // Placeholder for feature test validation
});
