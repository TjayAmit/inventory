<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'sku',
    'barcode',
    'name',
    'slug',
    'description',
    'category_id',
    'brand',
    'unit',
    'cost_price',
    'selling_price',
    'min_price',
    'reorder_level',
    'reorder_quantity',
    'is_active',
    'is_taxable',
    'is_trackable',
    'image_urls'
])]
#[Hidden([])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_price' => 'decimal:2',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'is_trackable' => 'boolean',
            'image_urls' => 'array',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the inventory records for the product.
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the purchase order items for the product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the sales items for the product.
     */
    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the inventory adjustments for the product.
     */
    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include trackable products.
     */
    public function scopeTrackable($query)
    {
        return $query->where('is_trackable', true);
    }

    /**
     * Scope a query to only include taxable products.
     */
    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    /**
     * Get the current stock across all branches.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->inventory()->sum('quantity_on_hand');
    }

    /**
     * Get the total stock value across all branches.
     */
    public function getTotalStockValueAttribute(): float
    {
        return $this->inventory()->sum(function ($inventory) {
            return $inventory->quantity_on_hand * $inventory->average_cost;
        });
    }

    /**
     * Check if product needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->getTotalStockAttribute() <= $this->reorder_level;
    }
}
