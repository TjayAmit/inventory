<?php

namespace App\Models;

use Database\Factories\PurchaseOrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'purchase_order_id',
    'product_id',
    'batch_number',
    'quantity_ordered',
    'quantity_received',
    'unit_cost',
    'total_cost',
    'tax_rate',
    'tax_amount',
    'discount_percent',
    'discount_amount',
    'line_total',
    'expiry_date',
    'notes'
])]
#[Hidden([])]
class PurchaseOrderItem extends Model
{
    /** @use HasFactory<PurchaseOrderItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'integer',
            'quantity_received' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'tax_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'expiry_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the remaining quantity to be received.
     */
    public function getQuantityRemainingAttribute(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    /**
     * Check if the item is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    /**
     * Check if the item is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->quantity_ordered === 0) {
            return 0;
        }

        return round(($this->quantity_received / $this->quantity_ordered) * 100, 2);
    }

    /**
     * Get the formatted unit cost.
     */
    public function getFormattedUnitCostAttribute(): string
    {
        return number_format($this->unit_cost, 4);
    }

    /**
     * Get the formatted line total.
     */
    public function getFormattedLineTotalAttribute(): string
    {
        return number_format($this->line_total, 2);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isFullyReceived()) {
            return 'Received';
        } elseif ($this->isPartiallyReceived()) {
            return 'Partially Received';
        } else {
            return 'Pending';
        }
    }

    /**
     * Scope a query to only include items that are not fully received.
     */
    public function scopePending($query)
    {
        return $query->whereColumn('quantity_received', '<', 'quantity_ordered');
    }

    /**
     * Scope a query to only include fully received items.
     */
    public function scopeReceived($query)
    {
        return $query->whereColumn('quantity_received', '>=', 'quantity_ordered');
    }

    /**
     * Scope a query to only include partially received items.
     */
    public function scopePartiallyReceived($query)
    {
        return $query->where('quantity_received', '>', 0)
                   ->whereColumn('quantity_received', '<', 'quantity_ordered');
    }
}
