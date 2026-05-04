<?php

namespace App\DTOs\PurchaseRequest;

use Illuminate\Http\Request;

class PurchaseRequestData
{
    public function __construct(
        public readonly string $request_number,
        public readonly int $branch_id,
        public readonly int $product_id,
        public readonly int $requested_quantity,
        public readonly string $status = 'pending',
        public readonly string $trigger_type = 'manual',
        public readonly ?string $notes = null,
        public readonly ?int $requested_by = null,
        public readonly ?int $reviewed_by = null,
        public readonly ?string $reviewed_at = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            request_number: $request->validated('request_number'),
            branch_id: $request->validated('branch_id'),
            product_id: $request->validated('product_id'),
            requested_quantity: $request->validated('requested_quantity'),
            status: $request->validated('status', 'pending'),
            trigger_type: $request->validated('trigger_type', 'manual'),
            notes: $request->validated('notes'),
            requested_by: $request->validated('requested_by'),
            reviewed_by: $request->validated('reviewed_by'),
            reviewed_at: $request->validated('reviewed_at'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
