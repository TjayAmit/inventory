<?php

namespace App\Models;

use Database\Factories\PurchaseOrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'purchase_order_id',
    'product_id',
    'purchase_request_id',
    'quantity_ordered',
    'quantity_received',
    'unit_cost',
    'total_cost'
])]
#[Hidden([])]
class PurchaseOrderItem extends Model
{
    /** @use HasFactory<PurchaseOrderItemFactory> */
    use HasFactory;

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
     * Get the purchase request that owns the item.
     */
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
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
