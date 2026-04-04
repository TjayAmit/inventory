<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'name', 'product_code', 'barcode', 'description', 'price', 'cost_price',
    'category_id', 'is_active', 'is_taxable', 'unit', 'weight', 'volume',
    'brand', 'manufacturer', 'supplier', 'reorder_point', 'max_stock', 'notes'
])]
#[Hidden([])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'volume' => 'decimal:3',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'reorder_point' => 'integer',
            'max_stock' => 'integer',
        ];
    }

    /**
     * Boot the model and add validation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            $product->validateBarcode();
        });
    }

    /**
     * Validate the barcode format.
     */
    public function validateBarcode(): void
    {
        if (empty($this->barcode)) {
            return;
        }

        // EAN-13 barcode validation (13 digits)
        if (!preg_match('/^\d{13}$/', $this->barcode)) {
            throw ValidationException::withMessages([
                'barcode' => 'Barcode must be exactly 13 digits (EAN-13 format).'
            ]);
        }

        // Validate EAN-13 checksum
        if (!$this->isValidEAN13Checksum($this->barcode)) {
            throw ValidationException::withMessages([
                'barcode' => 'Invalid barcode checksum.'
            ]);
        }
    }

    /**
     * Validate EAN-13 checksum.
     */
    private function isValidEAN13Checksum(string $barcode): bool
    {
        $digits = str_split($barcode);
        $checksum = (int) array_pop($digits);

        $sum = 0;
        foreach ($digits as $i => $digit) {
            $multiplier = ($i % 2 === 0) ? 1 : 3;
            $sum += (int) $digit * $multiplier;
        }

        $calculatedChecksum = (10 - ($sum % 10)) % 10;

        return $checksum === $calculatedChecksum;
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock records for the product.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the sale items for the product.
     * TODO: Implement when sales module is created
     */
    // public function saleItems(): HasMany
    // {
    //     return $this->hasMany(SaleItem::class);
    // }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include taxable products.
     */
    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    /**
     * Scope a query to search by name or product code.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('product_code', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('brand', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Find a product by barcode.
     */
    public static function findByBarcode(string $barcode): ?static
    {
        return static::where('barcode', $barcode)->first();
    }

    /**
     * Find a product by product code.
     */
    public static function findByProductCode(string $productCode): ?static
    {
        return static::where('product_code', $productCode)->first();
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get the formatted cost price.
     */
    public function getFormattedCostPriceAttribute(): string
    {
        return $this->cost_price ? '₱' . number_format($this->cost_price, 2) : 'N/A';
    }

    /**
     * Get the profit margin.
     */
    public function getProfitMarginAttribute(): float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return 0;
        }

        return (($this->price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get the formatted profit margin.
     */
    public function getFormattedProfitMarginAttribute(): string
    {
        return number_format($this->profit_margin, 2) . '%';
    }

    /**
     * Check if product is in stock (placeholder for when stock system is implemented).
     */
    public function getIsInStockAttribute(): bool
    {
        // This will be implemented when we create the Stock model
        return true;
    }

    /**
     * Get the current stock quantity (placeholder).
     */
    public function getCurrentStockAttribute(): int
    {
        // This will be implemented when we create the Stock model
        return 0;
    }

    /**
     * Generate a unique product code if not set.
     */
    public function generateProductCode(): void
    {
        if (empty($this->product_code)) {
            $prefix = 'PRD';
            $timestamp = now()->format('Ymd');
            $random = mt_rand(1000, 9999);
            $this->product_code = "{$prefix}{$timestamp}{$random}";
        }
    }

    /**
     * Generate a dummy EAN-13 barcode for testing purposes.
     */
    public static function generateDummyBarcode(): string
    {
        // Generate 12 random digits
        $digits = '';
        for ($i = 0; $i < 12; $i++) {
            $digits .= mt_rand(0, 9);
        }

        // Calculate checksum
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $multiplier = ($i % 2 === 0) ? 1 : 3;
            $sum += (int) $digits[$i] * $multiplier;
        }
        $checksum = (10 - ($sum % 10)) % 10;

        return $digits . $checksum;
    }
}
