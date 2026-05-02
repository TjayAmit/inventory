<?php

namespace App\DTOs\Supplier;

use Illuminate\Http\Request;

class SupplierData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $contact_person = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $mobile = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $country = null,
        public readonly ?string $tax_id = null,
        public readonly ?string $website = null,
        public readonly ?string $notes = null,
        public readonly ?float $credit_limit = null,
        public readonly int $payment_terms = 0,
        public readonly bool $is_active = true,
        public readonly bool $is_preferred = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            contact_person: $request->validated('contact_person'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            mobile: $request->validated('mobile'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            state: $request->validated('state'),
            postal_code: $request->validated('postal_code'),
            country: $request->validated('country'),
            tax_id: $request->validated('tax_id'),
            website: $request->validated('website'),
            notes: $request->validated('notes'),
            credit_limit: $request->validated('credit_limit'),
            payment_terms: $request->validated('payment_terms', 0),
            is_active: $request->validated('is_active', true),
            is_preferred: $request->validated('is_preferred', false),
        );
    }

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            code: $supplier->code,
            name: $supplier->name,
            contact_person: $supplier->contact_person,
            email: $supplier->email,
            phone: $supplier->phone,
            mobile: $supplier->mobile,
            address: $supplier->address,
            city: $supplier->city,
            state: $supplier->state,
            postal_code: $supplier->postal_code,
            country: $supplier->country,
            tax_id: $supplier->tax_id,
            website: $supplier->website,
            notes: $supplier->notes,
            credit_limit: $supplier->credit_limit,
            payment_terms: $supplier->payment_terms,
            is_active: $supplier->is_active,
            is_preferred: $supplier->is_preferred,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
