<?php

namespace App\Models;

use Database\Factories\InventoryAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'inventory_id',
    'adjusted_by',
    'approved_by',
    'reason_code',
    'quantity_before',
    'quantity_change',
    'quantity_after',
    'notes',
    'approved_at'
])]
#[Hidden([])]
class InventoryAdjustment extends Model
{
    /** @use HasFactory<InventoryAdjustmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_before' => 'integer',
            'quantity_change' => 'integer',
            'quantity_after' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the inventory that owns the adjustment.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who made the adjustment.
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Get the user who approved the adjustment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include adjustments by reason code.
     */
    public function scopeReason($query, $reasonCode)
    {
        return $query->where('reason_code', $reasonCode);
    }

    /**
     * Scope a query to only include pending adjustments.
     */
    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    /**
     * Scope a query to only include approved adjustments.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }    /**
     * Check if the adjustment is pending approval.
     */
    public function isPending(): bool
    {
        return is_null($this->approved_at);
    }

    /**
     * Check if the adjustment is approved.
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Get the adjustment type (increase or decrease).
     */
    public function getAdjustmentTypeAttribute(): string
    {
        return $this->quantity_change > 0 ? 'increase' : 'decrease';
    }

    /**
     * Get the reason description based on reason code.
     */
    public function getReasonDescriptionAttribute(): string
    {
        $reasons = [
            'damaged' => 'Damaged Goods',
            'expired' => 'Expired Goods',
            'count' => 'Stock Count Adjustment',
            'theft' => 'Theft/Loss',
            'return' => 'Customer Return',
            'correction' => 'Data Entry Correction',
        ];

        return $reasons[$this->reason_code] ?? $this->reason_code;
    }

    /**
     * Check if the adjustment is an increase.
     */
    public function isIncrease(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * Check if the adjustment is a decrease.
     */
    public function isDecrease(): bool
    {
        return $this->quantity_change < 0;
    }

    /**
     * Get the absolute value of the quantity change.
     */
    public function getAbsoluteQuantityChangeAttribute(): int
    {
        return abs($this->quantity_change);
    }
}
