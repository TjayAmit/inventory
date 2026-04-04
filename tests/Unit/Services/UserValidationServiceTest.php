<?php

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserValidationService;

beforeEach(function () {
    $this->userRepository = mock(UserRepositoryInterface::class);
    $this->validationService = new UserValidationService($this->userRepository);
});

it('validates strong password correctly', function () {
    $password = 'StrongPass123';
    $errors = $this->validationService->validatePasswordStrength($password);

    expect($errors)->toBeEmpty();
});

it('detects weak password - too short', function () {
    $password = '123';
    $errors = $this->validationService->validatePasswordStrength($password);

    expect($errors)->toContain('Password must be at least 8 characters long.');
});

it('detects weak password - no uppercase', function () {
    $password = 'weakpass123';
    $errors = $this->validationService->validatePasswordStrength($password);

    expect($errors)->toContain('Password must contain at least one uppercase letter.');
});

it('detects weak password - no lowercase', function () {
    $password = 'STRONGPASS123';
    $errors = $this->validationService->validatePasswordStrength($password);

    expect($errors)->toContain('Password must contain at least one lowercase letter.');
});

it('detects weak password - no numbers', function () {
    $password = 'StrongPassword';
    $errors = $this->validationService->validatePasswordStrength($password);

    expect($errors)->toContain('Password must contain at least one number.');
});

it('validates email format and uniqueness', function () {
    $this->userRepository->shouldReceive('emailExists')->with('test@example.com', null)->andReturn(false);
    
    $errors = $this->validationService->validateEmail('test@example.com');

    expect($errors)->toBeEmpty();
});

it('detects invalid email format', function () {
    $this->userRepository->shouldReceive('emailExists')->with('invalid-email', null)->andReturn(false);
    
    $errors = $this->validationService->validateEmail('invalid-email');

    expect($errors)->toContain('Invalid email format.');
});

it('detects existing email', function () {
    $this->userRepository->shouldReceive('emailExists')->with('existing@example.com', null)->andReturn(true);
    
    $errors = $this->validationService->validateEmail('existing@example.com');

    expect($errors)->toContain('Email already exists.');
});

it('validates email excluding specific user', function () {
    $this->userRepository->shouldReceive('emailExists')->with('user@example.com', 1)->andReturn(false);
    
    $errors = $this->validationService->validateEmail('user@example.com', 1);

    expect($errors)->toBeEmpty();
});

it('checks if validation errors exist', function () {
    expect($this->validationService->hasValidationErrors([]))->toBeFalse();
    expect($this->validationService->hasValidationErrors(['error']))->toBeTrue();
});

it('sanitizes user data correctly', function () {
    $data = [
        'name' => '  John Doe  ',
        'email' => '  JOHN@EXAMPLE.COM  ',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'roles' => ['cashier', 'admin', 123],
    ];

    $sanitized = $this->validationService->sanitizeUserData($data);

    expect($sanitized['name'])->toBe('John Doe');
    expect($sanitized['email'])->toBe('john@example.com');
    expect($sanitized['password'])->toBe('Password123');
    expect($sanitized['password_confirmation'])->toBe('Password123');
    expect($sanitized['roles'])->toBe(['cashier', 'admin']);
});
