<?php

namespace App\DTOs\Product;

use App\DTOs\Base\BaseDataTransferObject;

class ProductFiltersDTO extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $categoryId = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isTaxable = null,
        public readonly ?string $brand = null,
        public readonly ?string $supplier = null,
        public readonly ?bool $hasBarcode = null,
        public readonly ?bool $hasCostPrice = null,
        public readonly ?string $sortBy = 'name',
        public readonly ?string $sortDirection = 'asc',
        public readonly ?int $perPage = null,
        public readonly ?int $page = null
    ) {
        // No validation needed for filters DTO
    }

    public function validate(): null
    {
        // Filters DTOs don't need validation
    }

    protected function rules(): array
    {
        return [];
    }

    /**
     * Create from request data.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            isActive: isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : null,
            isTaxable: isset($data['is_taxable']) ? filter_var($data['is_taxable'], FILTER_VALIDATE_BOOLEAN) : null,
            brand: $data['brand'] ?? null,
            supplier: $data['supplier'] ?? null,
            hasBarcode: isset($data['has_barcode']) ? filter_var($data['has_barcode'], FILTER_VALIDATE_BOOLEAN) : null,
            hasCostPrice: isset($data['has_cost_price']) ? filter_var($data['has_cost_price'], FILTER_VALIDATE_BOOLEAN) : null,
            sortBy: $data['sort_by'] ?? 'name',
            sortDirection: $data['sort_direction'] ?? 'asc',
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : null,
            page: isset($data['page']) ? (int) $data['page'] : null
        );
    }

    /**
     * Get the search term.
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * Get the category ID.
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * Get the minimum price.
     */
    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    /**
     * Get the maximum price.
     */
    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    /**
     * Get the active status filter.
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Get the taxable status filter.
     */
    public function getIsTaxable(): ?bool
    {
        return $this->isTaxable;
    }

    /**
     * Get the brand filter.
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * Get the supplier filter.
     */
    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    /**
     * Get the has barcode filter.
     */
    public function getHasBarcode(): ?bool
    {
        return $this->hasBarcode;
    }

    /**
     * Get the has cost price filter.
     */
    public function getHasCostPrice(): ?bool
    {
        return $this->hasCostPrice;
    }

    /**
     * Get the sort by field.
     */
    public function getSortBy(): string
    {
        return $this->sortBy ?? 'name';
    }

    /**
     * Get the sort direction.
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection ?? 'asc';
    }

    /**
     * Get the per page value.
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    /**
     * Get the page value.
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * Check if any filters are applied.
     */
    public function hasFilters(): bool
    {
        return $this->search !== null ||
               $this->categoryId !== null ||
               $this->minPrice !== null ||
               $this->maxPrice !== null ||
               $this->isActive !== null ||
               $this->isTaxable !== null ||
               $this->brand !== null ||
               $this->supplier !== null ||
               $this->hasBarcode !== null ||
               $this->hasCostPrice !== null;
    }

    /**
     * Check if search filter is applied.
     */
    public function hasSearchFilter(): bool
    {
        return !empty($this->search);
    }

    /**
     * Check if category filter is applied.
     */
    public function hasCategoryFilter(): bool
    {
        return $this->categoryId !== null;
    }

    /**
     * Check if price filter is applied.
     */
    public function hasPriceFilter(): bool
    {
        return $this->minPrice !== null || $this->maxPrice !== null;
    }

    /**
     * Check if brand filter is applied.
     */
    public function hasBrandFilter(): bool
    {
        return !empty($this->brand);
    }

    /**
     * Check if supplier filter is applied.
     */
    public function hasSupplierFilter(): bool
    {
        return !empty($this->supplier);
    }

    /**
     * Check if sorting is applied (other than default).
     */
    public function hasCustomSorting(): bool
    {
        return $this->sortBy !== 'name' || $this->sortDirection !== 'asc';
    }

    /**
     * Get the valid sort fields.
     */
    public function getValidSortFields(): array
    {
        return [
            'name',
            'product_code',
            'price',
            'cost_price',
            'brand',
            'supplier',
            'created_at',
            'updated_at',
            'reorder_point',
            'max_stock'
        ];
    }

    /**
     * Validate sort field.
     */
    public function isValidSortField(): bool
    {
        return in_array($this->sortBy, $this->getValidSortFields());
    }

    /**
     * Validate sort direction.
     */
    public function isValidSortDirection(): bool
    {
        return in_array(strtolower($this->sortDirection), ['asc', 'desc']);
    }

    /**
     * Get sort options for dropdown.
     */
    public function getSortOptions(): array
    {
        return [
            'name' => 'Name',
            'product_code' => 'Product Code',
            'price' => 'Price',
            'cost_price' => 'Cost Price',
            'brand' => 'Brand',
            'supplier' => 'Supplier',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'reorder_point' => 'Reorder Point',
            'max_stock' => 'Max Stock'
        ];
    }

    /**
     * Convert to array for query building.
     */
    public function toQueryFilters(): array
    {
        $filters = [];

        if ($this->search) {
            $filters['search'] = $this->search;
        }

        if ($this->categoryId) {
            $filters['category_id'] = $this->categoryId;
        }

        if ($this->minPrice !== null) {
            $filters['min_price'] = $this->minPrice;
        }

        if ($this->maxPrice !== null) {
            $filters['max_price'] = $this->maxPrice;
        }

        if ($this->isActive !== null) {
            $filters['is_active'] = $this->isActive;
        }

        if ($this->isTaxable !== null) {
            $filters['is_taxable'] = $this->isTaxable;
        }

        if ($this->brand) {
            $filters['brand'] = $this->brand;
        }

        if ($this->supplier) {
            $filters['supplier'] = $this->supplier;
        }

        if ($this->hasBarcode !== null) {
            $filters['has_barcode'] = $this->hasBarcode;
        }

        if ($this->hasCostPrice !== null) {
            $filters['has_cost_price'] = $this->hasCostPrice;
        }

        return $filters;
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'categoryId' => $this->categoryId,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'isActive' => $this->isActive,
            'isTaxable' => $this->isTaxable,
            'brand' => $this->brand,
            'supplier' => $this->supplier,
            'hasBarcode' => $this->hasBarcode,
            'hasCostPrice' => $this->hasCostPrice,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'hasFilters' => $this->hasFilters(),
            'hasSearchFilter' => $this->hasSearchFilter(),
            'hasCategoryFilter' => $this->hasCategoryFilter(),
            'hasPriceFilter' => $this->hasPriceFilter(),
            'hasBrandFilter' => $this->hasBrandFilter(),
            'hasSupplierFilter' => $this->hasSupplierFilter(),
            'hasCustomSorting' => $this->hasCustomSorting(),
        ];
    }
}
