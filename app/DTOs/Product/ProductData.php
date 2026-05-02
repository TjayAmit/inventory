<?php

namespace App\DTOs\Product;

use Illuminate\Http\Request;

class ProductData
{
    public function __construct(
        public readonly string $sku,
        public readonly ?string $barcode = null,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?string $short_description = null,
        public readonly ?int $category_id = null,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly string $unit = 'piece',
        public readonly ?float $weight = null,
        public readonly ?string $dimensions = null,
        public readonly float $cost_price = 0.00,
        public readonly float $selling_price = 0.00,
        public readonly ?float $min_price = null,
        public readonly ?float $max_price = null,
        public readonly int $reorder_level = 0,
        public readonly int $reorder_quantity = 0,
        public readonly bool $is_active = true,
        public readonly bool $is_taxable = true,
        public readonly bool $is_trackable = true,
        public readonly bool $is_sellable = true,
        public readonly ?array $image_urls = null,
        public readonly ?array $attributes = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            sku: $request->validated('sku'),
            barcode: $request->validated('barcode'),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            short_description: $request->validated('short_description'),
            category_id: $request->validated('category_id'),
            brand: $request->validated('brand'),
            model: $request->validated('model'),
            unit: $request->validated('unit', 'piece'),
            weight: $request->validated('weight'),
            dimensions: $request->validated('dimensions'),
            cost_price: $request->validated('cost_price', 0.00),
            selling_price: $request->validated('selling_price', 0.00),
            min_price: $request->validated('min_price'),
            max_price: $request->validated('max_price'),
            reorder_level: $request->validated('reorder_level', 0),
            reorder_quantity: $request->validated('reorder_quantity', 0),
            is_active: $request->validated('is_active', true),
            is_taxable: $request->validated('is_taxable', true),
            is_trackable: $request->validated('is_trackable', true),
            is_sellable: $request->validated('is_sellable', true),
            image_urls: $request->validated('image_urls'),
            attributes: $request->validated('attributes'),
        );
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            sku: $product->sku,
            barcode: $product->barcode,
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            short_description: $product->short_description,
            category_id: $product->category_id,
            brand: $product->brand,
            model: $product->model,
            unit: $product->unit,
            weight: $product->weight,
            dimensions: $product->dimensions,
            cost_price: $product->cost_price,
            selling_price: $product->selling_price,
            min_price: $product->min_price,
            max_price: $product->max_price,
            reorder_level: $product->reorder_level,
            reorder_quantity: $product->reorder_quantity,
            is_active: $product->is_active,
            is_taxable: $product->is_taxable,
            is_trackable: $product->is_trackable,
            is_sellable: $product->is_sellable,
            image_urls: $product->image_urls,
            attributes: $product->attributes,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
