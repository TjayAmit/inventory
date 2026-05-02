<?php

namespace App\DTOs\Branch;

use Illuminate\Http\Request;

class BranchData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $country = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $manager_id = null,
        public readonly bool $is_active = true,
        public readonly bool $is_main_branch = false,
        public readonly ?string $timezone = null,
        public readonly ?string $currency = null,
        public readonly ?float $tax_rate = null,
        public readonly ?array $operating_hours = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            state: $request->validated('state'),
            postal_code: $request->validated('postal_code'),
            country: $request->validated('country'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            manager_id: $request->validated('manager_id'),
            is_active: $request->validated('is_active', true),
            is_main_branch: $request->validated('is_main_branch', false),
            timezone: $request->validated('timezone'),
            currency: $request->validated('currency'),
            tax_rate: $request->validated('tax_rate'),
            operating_hours: $request->validated('operating_hours'),
        );
    }

    public static function fromModel(Branch $branch): self
    {
        return new self(
            code: $branch->code,
            name: $branch->name,
            address: $branch->address,
            city: $branch->city,
            state: $branch->state,
            postal_code: $branch->postal_code,
            country: $branch->country,
            phone: $branch->phone,
            email: $branch->email,
            manager_id: $branch->manager_id,
            is_active: $branch->is_active,
            is_main_branch: $branch->is_main_branch,
            timezone: $branch->timezone,
            currency: $branch->currency,
            tax_rate: $branch->tax_rate,
            operating_hours: $branch->operating_hours,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
