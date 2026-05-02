<?php

namespace App\DTOs\ProductCategory;

use Illuminate\Http\Request;

class ProductCategoryData
{
    public function __construct(
        public readonly ?int $parent_id = null,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?string $image_url = null,
        public readonly int $sort_order = 0,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            parent_id: $request->validated('parent_id'),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            image_url: $request->validated('image_url'),
            sort_order: $request->validated('sort_order', 0),
            is_active: $request->validated('is_active', true),
        );
    }

    public static function fromModel(ProductCategory $category): self
    {
        return new self(
            parent_id: $category->parent_id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            image_url: $category->image_url,
            sort_order: $category->sort_order,
            is_active: $category->is_active,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
