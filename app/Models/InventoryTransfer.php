<?php

namespace App\Models;

use Database\Factories\InventoryTransferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transfer_number',
    'product_id',
    'from_branch_id',
    'to_branch_id',
    'inventory_batch_id',
    'created_by',
    'approved_by',
    'received_by',
    'status',
    'quantity',
    'quantity_sent',
    'quantity_received',
    'unit_cost',
    'total_cost',
    'reason',
    'notes',
    'approval_notes',
    'receiving_notes',
    'transfer_date',
    'approved_at',
    'sent_at',
    'received_at'
])]
#[Hidden([])]
class InventoryTransfer extends Model
{
    /** @use HasFactory<InventoryTransferFactory> */
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
            'quantity_sent' => 'integer',
            'quantity_received' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'transfer_date' => 'datetime',
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::saving(function ($transfer) {
            $transfer->quantity_sent = $transfer->quantity;
        });
    }

    /**
     * Get the product that owns the transfer.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the from branch that owns the transfer.
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the to branch that owns the transfer.
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the inventory batch that owns the transfer.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    /**
     * Get the user that created the transfer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that approved the transfer.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user that received the transfer.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to only include transfers with specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft transfers.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include pending approval transfers.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope a query to only include approved transfers.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include in transit transfers.
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Scope a query to only include received transfers.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope a query to only include cancelled transfers.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
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
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the remaining quantity to be sent.
     */
    public function getQuantityRemainingAttribute(): int
    {
        return max(0, $this->quantity - $this->quantity_sent);
    }

    /**
     * Get the remaining quantity to be received.
     */
    public function getQuantityToReceiveAttribute(): int
    {
        return max(0, $this->quantity_sent - $this->quantity_received);
    }

    /**
     * Check if the transfer is fully sent.
     */
    public function isFullySent(): bool
    {
        return $this->quantity_sent >= $this->quantity;
    }

    /**
     * Check if the transfer is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_sent;
    }

    /**
     * Check if the transfer is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_sent;
    }

    /**
     * Check if the transfer is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the transfer is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if the transfer can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if the transfer can be sent.
     */
    public function canBeSent(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the transfer can be received.
     */
    public function canBeReceived(): bool
    {
        return $this->status === 'in_transit';
    }

    /**
     * Check if the transfer can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval', 'approved']);
    }

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->quantity_sent === 0) {
            return 0;
        }

        return round(($this->quantity_received / $this->quantity_sent) * 100, 2);
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
     * Scope a query to only include transfers within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transfer_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include transfers from a specific branch.
     */
    public function scopeFromBranch($query, $branchId)
    {
        return $query->where('from_branch_id', $branchId);
    }

    /**
     * Scope a query to only include transfers to a specific branch.
     */
    public function scopeToBranch($query, $branchId)
    {
        return $query->where('to_branch_id', $branchId);
    }

    /**
     * Scope a query to only include transfers for a specific product.
     */
    public function scopeProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include transfers created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include transfers approved by a specific user.
     */
    public function scopeApprovedBy($query, $userId)
    {
        return $query->where('approved_by', $userId);
    }

    /**
     * Scope a query to only include transfers received by a specific user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('received_by', $userId);
    }
}
