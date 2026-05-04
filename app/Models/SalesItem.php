<?php

namespace App\Models;

use Database\Factories\SalesItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sales_order_id',
    'product_id',
    'inventory_batch_id',
    'quantity',
    'unit_price',
    'unit_cost',
    'discount_amount',
    'tax_amount',
    'total_price',
    'total_cost',
    'profit'
])]
#[Hidden([])]
class SalesItem extends Model
{
    /** @use HasFactory<SalesItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:4',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    /**
     * Get the sales order that owns the item.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the inventory batch that owns the item.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    /**
     * Get the total price including tax.
     */
    public function getTotalPriceWithTaxAttribute(): float
    {
        return $this->total_price + $this->tax_amount;
    }

    /**
     * Get the profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_price == 0) {
            return 0;
        }

        return round(($this->profit / $this->total_price) * 100, 2);
    }

    /**
     * Get the cost per unit including all allocations.
     */
    public function getEffectiveUnitCostAttribute(): float
    {
        return $this->quantity > 0 ? $this->total_cost / $this->quantity : 0;
    }

    /**
     * Get the effective price per unit after discounts.
     */
    public function getEffectiveUnitPriceAttribute(): float
    {
        return $this->quantity > 0 ? $this->total_price / $this->quantity : 0;
    }
}
