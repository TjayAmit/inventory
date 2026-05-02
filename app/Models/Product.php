<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku',
    'barcode',
    'name',
    'slug',
    'description',
    'short_description',
    'category_id',
    'brand',
    'model',
    'unit',
    'weight',
    'dimensions',
    'cost_price',
    'selling_price',
    'min_price',
    'max_price',
    'reorder_level',
    'reorder_quantity',
    'is_active',
    'is_taxable',
    'is_trackable',
    'is_sellable',
    'image_urls',
    'attributes'
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
            'max_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'is_trackable' => 'boolean',
            'is_sellable' => 'boolean',
            'image_urls' => 'array',
            'attributes' => 'array',
            'deleted_at' => 'datetime',
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
     * Get the inventory transfers for the product.
     */
    public function inventoryTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include sellable products.
     */
    public function scopeSellable($query)
    {
        return $query->where('is_sellable', true);
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
        return $this->inventory()->sum('total_cost');
    }

    /**
     * Check if product needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->getTotalStockAttribute() <= $this->reorder_level;
    }
}
