<?php

namespace App\DTOs\Supplier;

use Illuminate\Http\Request;

class SupplierData
{
    public function __construct(
        public readonly string $supplier_code,
        public readonly string $name,
        public readonly ?string $contact_person = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $payment_terms = null,
        public readonly bool $is_active = true,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            supplier_code: $request->validated('supplier_code'),
            name: $request->validated('name'),
            contact_person: $request->validated('contact_person'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            payment_terms: $request->validated('payment_terms'),
            is_active: $request->validated('is_active', true),
            notes: $request->validated('notes'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
