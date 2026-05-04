<?php

namespace App\DTOs\ProductCategory;

use Illuminate\Http\Request;

class ProductCategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?int $parent_id = null,
        public readonly int $sort_order = 0,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            parent_id: $request->validated('parent_id'),
            sort_order: $request->validated('sort_order', 0),
            is_active: $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
