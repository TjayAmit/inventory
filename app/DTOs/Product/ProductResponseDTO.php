<?php

namespace App\DTOs\Product;

use App\DTOs\Base\BaseDataTransferObject;

class ProductResponseDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $productCode,
        public readonly ?string $barcode,
        public readonly ?string $description,
        public readonly float $price,
        public readonly ?float $costPrice,
        public readonly ?int $categoryId,
        public readonly ?string $categoryName,
        public readonly bool $isActive,
        public readonly bool $isTaxable,
        public readonly string $unit,
        public readonly ?float $weight,
        public readonly ?float $volume,
        public readonly ?string $brand,
        public readonly ?string $manufacturer,
        public readonly ?string $supplier,
        public readonly int $reorderPoint,
        public readonly int $maxStock,
        public readonly ?string $notes,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?int $currentStock = null,
        public readonly ?float $profitMargin = null,
        public readonly ?string $formattedPrice = null,
        public readonly ?string $formattedCostPrice = null,
        public readonly ?string $formattedProfitMargin = null,
        public readonly ?bool $isInStock = null
    ) {
        // No validation needed for response DTO
    }

    protected function validate(): void
    {
        // Response DTOs don't need validation
    }

    protected function rules(): array
    {
        return [];
    }

    /**
     * Create from a Product model.
     */
    public static function fromModel($product, bool $includeCategory = false, bool $includeStock = false): self
    {
        $categoryName = null;
        $currentStock = null;
        $isInStock = null;

        if ($includeCategory && $product->relationLoaded('category') && $product->category) {
            $categoryName = $product->category->name;
        }

        if ($includeStock) {
            // This will be implemented when we create the Stock model
            $currentStock = $product->current_stock ?? 0;
            $isInStock = $product->is_in_stock ?? true;
        }

        $profitMargin = null;
        if ($product->cost_price && $product->cost_price > 0) {
            $profitMargin = (($product->price - $product->cost_price) / $product->cost_price) * 100;
        }

        return new self(
            id: $product->id,
            name: $product->name,
            productCode: $product->product_code,
            barcode: $product->barcode,
            description: $product->description,
            price: $product->price,
            costPrice: $product->cost_price,
            categoryId: $product->category_id,
            categoryName: $categoryName,
            isActive: $product->is_active,
            isTaxable: $product->is_taxable,
            unit: $product->unit,
            weight: $product->weight,
            volume: $product->volume,
            brand: $product->brand,
            manufacturer: $product->manufacturer,
            supplier: $product->supplier,
            reorderPoint: $product->reorder_point,
            maxStock: $product->max_stock,
            notes: $product->notes,
            createdAt: $product->created_at->toISOString(),
            updatedAt: $product->updated_at->toISOString(),
            currentStock: $currentStock,
            profitMargin: $profitMargin,
            formattedPrice: '₱' . number_format($product->price, 2),
            formattedCostPrice: $product->cost_price ? '₱' . number_format($product->cost_price, 2) : null,
            formattedProfitMargin: $profitMargin ? number_format($profitMargin, 2) . '%' : null,
            isInStock: $isInStock
        );
    }

    /**
     * Create a simple version for API responses.
     */
    public static function simple($product): self
    {
        $profitMargin = null;
        if ($product->cost_price && $product->cost_price > 0) {
            $profitMargin = (($product->price - $product->cost_price) / $product->cost_price) * 100;
        }

        return new self(
            id: $product->id,
            name: $product->name,
            productCode: $product->product_code,
            barcode: $product->barcode,
            description: $product->description,
            price: $product->price,
            costPrice: $product->cost_price,
            categoryId: $product->category_id,
            categoryName: $product->category?->name,
            isActive: $product->is_active,
            isTaxable: $product->is_taxable,
            unit: $product->unit,
            weight: $product->weight,
            volume: $product->volume,
            brand: $product->brand,
            manufacturer: $product->manufacturer,
            supplier: $product->supplier,
            reorderPoint: $product->reorder_point,
            maxStock: $product->max_stock,
            notes: $product->notes,
            createdAt: $product->created_at->toISOString(),
            updatedAt: $product->updated_at->toISOString(),
            profitMargin: $profitMargin,
            formattedPrice: '₱' . number_format($product->price, 2),
            formattedCostPrice: $product->cost_price ? '₱' . number_format($product->cost_price, 2) : null,
            formattedProfitMargin: $profitMargin ? number_format($profitMargin, 2) . '%' : null
        );
    }

    /**
     * Create a minimal version for dropdown/select lists.
     */
    public static function minimal($product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            productCode: $product->product_code,
            barcode: $product->barcode,
            description: null,
            price: $product->price,
            costPrice: null,
            categoryId: $product->category_id,
            categoryName: null,
            isActive: $product->is_active,
            isTaxable: true,
            unit: $product->unit,
            weight: null,
            volume: null,
            brand: null,
            manufacturer: null,
            supplier: null,
            reorderPoint: 0,
            maxStock: 0,
            notes: null,
            createdAt: $product->created_at->toISOString(),
            updatedAt: $product->updated_at->toISOString(),
            formattedPrice: '₱' . number_format($product->price, 2)
        );
    }

    /**
     * Get the product ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the product code.
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * Get the barcode.
     */
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    /**
     * Get the description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the price.
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Get the cost price.
     */
    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    /**
     * Get the category ID.
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * Get the category name.
     */
    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    /**
     * Get the active status.
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get the taxable status.
     */
    public function getIsTaxable(): bool
    {
        return $this->isTaxable;
    }

    /**
     * Get the unit.
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * Get the weight.
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Get the volume.
     */
    public function getVolume(): ?float
    {
        return $this->volume;
    }

    /**
     * Get the brand.
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * Get the manufacturer.
     */
    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    /**
     * Get the supplier.
     */
    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    /**
     * Get the reorder point.
     */
    public function getReorderPoint(): int
    {
        return $this->reorderPoint;
    }

    /**
     * Get the max stock.
     */
    public function getMaxStock(): int
    {
        return $this->maxStock;
    }

    /**
     * Get the notes.
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Get the creation date.
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Get the update date.
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Get the current stock.
     */
    public function getCurrentStock(): ?int
    {
        return $this->currentStock;
    }

    /**
     * Get the profit margin.
     */
    public function getProfitMargin(): ?float
    {
        return $this->profitMargin;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPrice(): ?string
    {
        return $this->formattedPrice;
    }

    /**
     * Get the formatted cost price.
     */
    public function getFormattedCostPrice(): ?string
    {
        return $this->formattedCostPrice;
    }

    /**
     * Get the formatted profit margin.
     */
    public function getFormattedProfitMargin(): ?string
    {
        return $this->formattedProfitMargin;
    }

    /**
     * Get the in stock status.
     */
    public function getIsInStock(): ?bool
    {
        return $this->isInStock;
    }

    /**
     * Check if this product has a barcode.
     */
    public function hasBarcode(): bool
    {
        return !empty($this->barcode);
    }

    /**
     * Check if this product has a cost price.
     */
    public function hasCostPrice(): bool
    {
        return $this->costPrice !== null;
    }

    /**
     * Check if this product is assigned to a category.
     */
    public function hasCategory(): bool
    {
        return $this->categoryId !== null;
    }

    /**
     * Check if this product has physical dimensions.
     */
    public function hasPhysicalDimensions(): bool
    {
        return $this->weight !== null || $this->volume !== null;
    }

    /**
     * Check if this product is active.
     */
    public function isActiveProduct(): bool
    {
        return $this->isActive;
    }

    /**
     * Check if this product is taxable.
     */
    public function isTaxableProduct(): bool
    {
        return $this->isTaxable;
    }

    /**
     * Check if this product has profit information.
     */
    public function hasProfitInfo(): bool
    {
        return $this->profitMargin !== null;
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'productCode' => $this->productCode,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'price' => $this->price,
            'costPrice' => $this->costPrice,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'isActive' => $this->isActive,
            'isTaxable' => $this->isTaxable,
            'unit' => $this->unit,
            'weight' => $this->weight,
            'volume' => $this->volume,
            'brand' => $this->brand,
            'manufacturer' => $this->manufacturer,
            'supplier' => $this->supplier,
            'reorderPoint' => $this->reorderPoint,
            'maxStock' => $this->maxStock,
            'notes' => $this->notes,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'currentStock' => $this->currentStock,
            'profitMargin' => $this->profitMargin,
            'formattedPrice' => $this->formattedPrice,
            'formattedCostPrice' => $this->formattedCostPrice,
            'formattedProfitMargin' => $this->formattedProfitMargin,
            'isInStock' => $this->isInStock,
            'hasBarcode' => $this->hasBarcode(),
            'hasCostPrice' => $this->hasCostPrice(),
            'hasCategory' => $this->hasCategory(),
            'hasPhysicalDimensions' => $this->hasPhysicalDimensions(),
            'hasProfitInfo' => $this->hasProfitInfo(),
        ];
    }
}
