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
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $manager_id = null,
        public readonly bool $is_active = true,
        public readonly bool $is_main_branch = false,
        public readonly ?string $timezone = null,
        public readonly ?string $currency = null,
        public readonly ?float $tax_rate = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            manager_id: $request->validated('manager_id'),
            is_active: $request->validated('is_active', true),
            is_main_branch: $request->validated('is_main_branch', false),
            timezone: $request->validated('timezone'),
            currency: $request->validated('currency'),
            tax_rate: $request->validated('tax_rate'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
