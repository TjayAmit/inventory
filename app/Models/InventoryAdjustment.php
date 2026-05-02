<?php

namespace App\Models;

use Database\Factories\InventoryAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'adjustment_number',
    'product_id',
    'branch_id',
    'inventory_batch_id',
    'created_by',
    'approved_by',
    'adjustment_type',
    'status',
    'quantity_before',
    'quantity_adjusted',
    'quantity_after',
    'unit_cost',
    'total_cost',
    'reason',
    'notes',
    'approval_notes',
    'adjustment_date',
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
            'quantity_adjusted' => 'integer',
            'quantity_after' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'adjustment_date' => 'datetime',
            'approved_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::saving(function ($adjustment) {
            $adjustment->quantity_after = $adjustment->quantity_before + $adjustment->quantity_adjusted;
            $adjustment->total_cost = $adjustment->unit_cost * abs($adjustment->quantity_adjusted);
        });
    }

    /**
     * Get the product that owns the adjustment.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch that owns the adjustment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inventory batch that owns the adjustment.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    /**
     * Get the user that created the adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that approved the adjustment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include adjustments with specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft adjustments.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include pending approval adjustments.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope a query to only include approved adjustments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected adjustments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include stock in adjustments.
     */
    public function scopeStockIn($query)
    {
        return $query->where('adjustment_type', 'stock_in');
    }

    /**
     * Scope a query to only include stock out adjustments.
     */
    public function scopeStockOut($query)
    {
        return $query->where('adjustment_type', 'stock_out');
    }

    /**
     * Scope a query to only include damage adjustments.
     */
    public function scopeDamage($query)
    {
        return $query->where('adjustment_type', 'damage');
    }

    /**
     * Scope a query to only include loss adjustments.
     */
    public function scopeLoss($query)
    {
        return $query->where('adjustment_type', 'loss');
    }

    /**
     * Scope a query to only include theft adjustments.
     */
    public function scopeTheft($query)
    {
        return $query->where('adjustment_type', 'theft');
    }

    /**
     * Scope a query to only include count corrections.
     */
    public function scopeCountCorrection($query)
    {
        return $query->where('adjustment_type', 'count_correction');
    }

    /**
     * Scope a query to only include expiry adjustments.
     */
    public function scopeExpiry($query)
    {
        return $query->where('adjustment_type', 'expiry');
    }

    /**
     * Get the adjustment type label.
     */
    public function getAdjustmentTypeLabelAttribute(): string
    {
        return [
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'damage' => 'Damage',
            'loss' => 'Loss',
            'theft' => 'Theft',
            'count_correction' => 'Count Correction',
            'expiry' => 'Expiry',
        ][$this->adjustment_type] ?? $this->adjustment_type;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ][$this->status] ?? $this->status;
    }

    /**
     * Check if the adjustment is a stock increase.
     */
    public function isStockIncrease(): bool
    {
        return $this->quantity_adjusted > 0;
    }

    /**
     * Check if the adjustment is a stock decrease.
     */
    public function isStockDecrease(): bool
    {
        return $this->quantity_adjusted < 0;
    }

    /**
     * Check if the adjustment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the adjustment is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if the adjustment can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if the adjustment is a negative adjustment.
     */
    public function isNegativeAdjustment(): bool
    {
        return in_array($this->adjustment_type, ['stock_out', 'damage', 'loss', 'theft', 'expiry']);
    }

    /**
     * Get the absolute quantity adjusted.
     */
    public function getAbsoluteQuantityAdjustedAttribute(): int
    {
        return abs($this->quantity_adjusted);
    }

    /**
     * Get the formatted unit cost.
     */
    public function getFormattedUnitCostAttribute(): string
    {
        return number_format($this->unit_cost, 4);
    }

    /**
     * Get the formatted total cost.
     */
    public function getFormattedTotalCostAttribute(): string
    {
        return number_format($this->total_cost, 2);
    }

    /**
     * Scope a query to only include adjustments within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('adjustment_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include adjustments for a specific product.
     */
    public function scopeProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include adjustments for a specific branch.
     */
    public function scopeBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include adjustments created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include adjustments approved by a specific user.
     */
    public function scopeApprovedBy($query, $userId)
    {
        return $query->where('approved_by', $userId);
    }
}
