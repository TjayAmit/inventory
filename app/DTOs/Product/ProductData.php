<?php

namespace App\DTOs\Product;

use Illuminate\Http\Request;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly ?string $barcode = null,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?int $category_id = null,
        public readonly ?string $brand = null,
        public readonly string $unit = 'piece',
        public readonly float $cost_price = 0.00,
        public readonly float $selling_price = 0.00,
        public readonly ?float $min_price = null,
        public readonly int $reorder_level = 0,
        public readonly int $reorder_quantity = 0,
        public readonly bool $is_active = true,
        public readonly bool $is_taxable = true,
        public readonly bool $is_trackable = true,
        public readonly ?array $image_urls = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            sku: $request->validated('sku'),
            barcode: $request->validated('barcode'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            category_id: $request->validated('category_id'),
            brand: $request->validated('brand'),
            unit: $request->validated('unit', 'piece'),
            cost_price: $request->validated('cost_price'),
            selling_price: $request->validated('selling_price'),
            min_price: $request->validated('min_price'),
            reorder_level: $request->validated('reorder_level'),
            reorder_quantity: $request->validated('reorder_quantity'),
            is_active: $request->validated('is_active', true),
            is_taxable: $request->validated('is_taxable', true),
            is_trackable: $request->validated('is_trackable', true),
            image_urls: $request->validated('image_urls'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
