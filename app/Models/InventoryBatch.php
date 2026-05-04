<?php

namespace App\Models;

use Database\Factories\InventoryBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'inventory_id',
    'batch_number',
    'purchase_order_item_id',
    'quantity',
    'quantity_remaining',
    'unit_cost',
    'manufacture_date',
    'expiry_date',
    'received_date',
    'received_by',
    'location',
    'notes',
    'is_active'
])]
#[Hidden([])]
class InventoryBatch extends Model
{
    /** @use HasFactory<InventoryBatchFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_remaining' => 'integer',
            'unit_cost' => 'decimal:4',
            'manufacture_date' => 'date',
            'expiry_date' => 'date',
            'received_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the inventory that owns the batch.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the purchase order item that owns the batch.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Get the user who received the batch.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to only include active batches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include expired batches.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope a query to only include batches expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Check if the batch is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the batch is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->lte(now()->addDays($days));
    }

    /**
     * Get the remaining quantity as a percentage.
     */
    public function getRemainingPercentageAttribute(): float
    {
        if ($this->quantity === 0) {
            return 0;
        }

        return round(($this->quantity_remaining / $this->quantity) * 100, 2);
    }

    /**
     * Get the total value of the batch.
     */
    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * Get the remaining value of the batch.
     */
    public function getRemainingValueAttribute(): float
    {
        return $this->quantity_remaining * $this->unit_cost;
    }

    /**
     * Consume quantity from the batch.
     */
    public function consume(int $quantity): bool
    {
        if ($this->quantity_remaining >= $quantity) {
            $this->quantity_remaining -= $quantity;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Check if the batch has sufficient quantity.
     */
    public function hasSufficientQuantity(int $quantity): bool
    {
        return $this->quantity_remaining >= $quantity;
    }
}
