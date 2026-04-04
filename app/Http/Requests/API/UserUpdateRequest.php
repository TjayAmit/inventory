<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit users');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ];
    }

    /**
     * Get custom error messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.confirmed' => 'The password confirmation does not match.',
            'roles.required' => 'At least one role must be selected.',
            'roles.min' => 'At least one role must be selected.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'password_confirmation' => 'password confirmation',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Prevent users from removing their own admin role
            $targetUserId = $this->route('user');
            if ($targetUserId && $targetUserId == $this->user()->id && 
                $this->user()->hasRole('admin') && !in_array('admin', $this->roles)) {
                $validator->errors()->add('roles', 'You cannot remove your own admin role.');
            }
        });
    }

    /**
     * Get the validated data with additional processing.
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();
        
        // Add password_confirmation if it exists
        if ($this->has('password_confirmation')) {
            $data['password_confirmation'] = $this->input('password_confirmation');
        }

        // Additional business logic validation
        if (in_array('admin', $data['roles']) && !$this->user()->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can assign admin role.');
        }

        return $data;
    }
}
