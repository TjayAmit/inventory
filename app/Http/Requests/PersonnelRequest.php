<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone'     => ['nullable', 'string', 'max:20'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role'      => ['nullable', 'string', 'in:admin,owner,store_manager,cashier,warehouse_staff'],
            'is_active' => ['boolean'],
        ];

        $rules['password'] = $isUpdate
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $rules;
    }
}
