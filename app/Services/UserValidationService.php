<?php

namespace App\Services;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserValidationService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Validate user creation data.
     */
    public function validateUserCreation(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'roles.required' => 'At least one role must be selected.',
            'roles.min' => 'At least one role must be selected.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate user update data.
     */
    public function validateUserUpdate(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'roles.required' => 'At least one role must be selected.',
            'roles.min' => 'At least one role must be selected.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate password strength.
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        return $errors;
    }

    /**
     * Validate email format and uniqueness.
     */
    public function validateEmail(string $email, ?int $excludeUserId = null): array
    {
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if ($this->userRepository->emailExists($email, $excludeUserId)) {
            $errors[] = 'Email already exists.';
        }

        return $errors;
    }

    /**
     * Validate user data for business rules.
     */
    public function validateBusinessRules(array $data, ?User $currentUser = null, ?User $targetUser = null): array
    {
        $errors = [];

        // Rule: Non-admin users cannot create admin users
        if (isset($data['roles']) && in_array('admin', $data['roles'])) {
            if (!$currentUser?->hasRole('admin')) {
                $errors[] = 'Only administrators can create admin users.';
            }
        }

        // Rule: Users cannot delete themselves
        if ($targetUser && $currentUser && $targetUser->id === $currentUser->id) {
            $errors[] = 'You cannot perform this action on your own account.';
        }

        // Rule: Non-admin users cannot manage admin users
        if ($targetUser && $targetUser->hasRole('admin') && !$currentUser?->hasRole('admin')) {
            $errors[] = 'Only administrators can manage admin users.';
        }

        return $errors;
    }

    /**
     * Validate DTO for user creation.
     */
    public function validateCreateUserDTO(CreateUserDTO $dto, ?User $currentUser = null): array
    {
        $errors = [];

        // Validate password strength
        $passwordErrors = $this->validatePasswordStrength($dto->password);
        $errors = array_merge($errors, $passwordErrors);

        // Validate email
        $emailErrors = $this->validateEmail($dto->email);
        $errors = array_merge($errors, $emailErrors);

        // Validate business rules
        $businessErrors = $this->validateBusinessRules([
            'roles' => $dto->getRoles()
        ], $currentUser);

        $errors = array_merge($errors, $businessErrors);

        return $errors;
    }

    /**
     * Validate DTO for user update.
     */
    public function validateUpdateUserDTO(UpdateUserDTO $dto, ?User $currentUser = null, ?User $targetUser = null): array
    {
        $errors = [];

        // Validate password if provided
        if ($dto->hasPassword()) {
            $passwordErrors = $this->validatePasswordStrength($dto->password);
            $errors = array_merge($errors, $passwordErrors);
        }

        // Validate email
        $emailErrors = $this->validateEmail($dto->email, $dto->userId);
        $errors = array_merge($errors, $emailErrors);

        // Validate business rules
        $businessErrors = $this->validateBusinessRules([
            'roles' => $dto->getRoles()
        ], $currentUser, $targetUser);

        $errors = array_merge($errors, $businessErrors);

        return $errors;
    }

    /**
     * Check if validation errors exist.
     */
    public function hasValidationErrors(array $errors): bool
    {
        return !empty($errors);
    }

    /**
     * Throw validation exception if errors exist.
     */
    public function throwValidationErrors(array $errors): void
    {
        if ($this->hasValidationErrors($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Sanitize user input data.
     */
    public function sanitizeUserData(array $data): array
    {
        return [
            'name' => trim(strip_tags($data['name'] ?? '')),
            'email' => strtolower(trim(strip_tags($data['email'] ?? ''))),
            'password' => $data['password'] ?? null,
            'password_confirmation' => $data['password_confirmation'] ?? null,
            'roles' => array_filter($data['roles'] ?? [], 'is_string'),
        ];
    }
}
