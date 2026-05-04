<?php

namespace App\Models;

use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_number',
    'supplier_id',
    'branch_id',
    'created_by',
    'order_date',
    'expected_date',
    'status',
    'subtotal',
    'tax_amount',
    'total_amount',
    'notes',
    'cancelled_at',
    'cancelled_by'
])]
#[Hidden([])]
class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the supplier that owns the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the branch that owns the purchase order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user that created the purchase order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Scope a query to only include orders with specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft orders.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include sent orders.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include received orders.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if the order is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->expected_date && 
               $this->expected_date->isPast() && 
               !in_array($this->status, ['received', 'cancelled']);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'confirmed' => 'Confirmed',
            'partial' => 'Partially Received',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the total quantity of all items.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity_ordered');
    }

    /**
     * Get the total received quantity of all items.
     */
    public function getTotalReceivedQuantityAttribute(): int
    {
        return $this->items->sum('quantity_received');
    }

    /**
     * Check if the order is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        $totalOrdered = $this->getTotalQuantityAttribute();
        $totalReceived = $this->getTotalReceivedQuantityAttribute();
        
        return $totalReceived > 0 && $totalReceived < $totalOrdered;
    }

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        $totalOrdered = $this->getTotalQuantityAttribute();
        
        if ($totalOrdered === 0) {
            return 0;
        }
        
        $totalReceived = $this->getTotalReceivedQuantityAttribute();
        
        return round(($totalReceived / $totalOrdered) * 100, 2);
    }
}
