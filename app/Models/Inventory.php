<?php

namespace App\Models;

use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'product_id',
    'branch_id',
    'quantity_on_hand',
    'quantity_reserved',
    'quantity_available',
    'average_cost',
    'last_count_date',
    'last_received_date',
    'is_active'
])]
#[Hidden([])]
class Inventory extends Model
{
    /** @use HasFactory<InventoryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'quantity_available' => 'integer',
            'average_cost' => 'decimal:4',
            'last_count_date' => 'date',
            'last_received_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the product that owns the inventory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch that owns the inventory.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inventory batches for the inventory.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Get the sales items for the inventory.
     */
    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the inventory adjustments for the inventory.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /**
     * Scope a query to only include active inventory.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->where('quantity_on_hand', '<=', function ($query) {
            $query->select('reorder_level')
                  ->from('products')
                  ->whereColumn('products.id', 'inventory.product_id');
        });
    }

    /**
     * Scope a query to only include out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }

    /**
     * Check if inventory is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->product->reorder_level;
    }

    /**
     * Check if inventory is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    /**
     * Get available quantity for sale.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    /**
     * Update available quantity based on on hand and reserved.
     */
    public function updateAvailableQuantity(): void
    {
        $this->quantity_available = $this->getAvailableQuantityAttribute();
        $this->save();
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(int $quantity): bool
    {
        if ($this->getAvailableQuantityAttribute() >= $quantity) {
            $this->quantity_reserved += $quantity;
            $this->updateAvailableQuantity();
            return true;
        }
        return false;
    }

    /**
     * Release reserved stock.
     */
    public function releaseStock(int $quantity): void
    {
        $this->quantity_reserved = max(0, $this->quantity_reserved - $quantity);
        $this->updateAvailableQuantity();
    }

    /**
     * Consume stock from inventory.
     */
    public function consumeStock(int $quantity): bool
    {
        if ($this->quantity_on_hand >= $quantity) {
            $this->quantity_on_hand -= $quantity;
            $this->quantity_reserved = max(0, $this->quantity_reserved - $quantity);
            $this->updateAvailableQuantity();
            return true;
        }
        return false;
    }

    /**
     * Add stock to inventory.
     */
    public function addStock(int $quantity, float $unitCost): void
    {
        $this->quantity_on_hand += $quantity;
        $this->last_received_date = now();
        $this->updateAvailableQuantity();
    }
}
