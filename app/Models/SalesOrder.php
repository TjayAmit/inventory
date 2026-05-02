<?php

namespace App\Models;

use Database\Factories\SalesOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_number',
    'customer_id',
    'branch_id',
    'created_by',
    'cashier_id',
    'status',
    'payment_status',
    'order_type',
    'order_date',
    'subtotal',
    'tax_amount',
    'discount_amount',
    'shipping_amount',
    'total_amount',
    'paid_amount',
    'change_amount',
    'notes',
    'internal_notes',
    'customer_reference'
])]
#[Hidden([])]
class SalesOrder extends Model
{
    /** @use HasFactory<SalesOrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the customer that owns the sales order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch that owns the sales order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user that created the sales order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the cashier that processed the sales order.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the items for the sales order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the payments for the sales order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include orders with specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed orders.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include paid orders.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include refunded orders.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope a query to only include sales.
     */
    public function scopeSales($query)
    {
        return $query->where('order_type', 'sale');
    }

    /**
     * Scope a query to only include returns.
     */
    public function scopeReturns($query)
    {
        return $query->where('order_type', 'return');
    }

    /**
     * Scope a query to only include exchanges.
     */
    public function scopeExchanges($query)
    {
        return $query->where('order_type', 'exchange');
    }

    /**
     * Get the remaining amount to be paid.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Check if the order is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if the order is partially paid.
     */
    public function isPartiallyPaid(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'paid' => 'Paid',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the payment status label.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
        ][$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get the order type label.
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return [
            'sale' => 'Sale',
            'return' => 'Return',
            'exchange' => 'Exchange',
        ][$this->order_type] ?? $this->order_type;
    }

    /**
     * Get the total quantity of all items.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the total profit of the order.
     */
    public function getTotalProfitAttribute(): float
    {
        return $this->items->sum('line_profit');
    }

    /**
     * Get the total cost of the order.
     */
    public function getTotalCostAttribute(): float
    {
        return $this->items->sum('line_cost');
    }

    /**
     * Get the profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_amount == 0) {
            return 0;
        }

        return round(($this->getTotalProfitAttribute() / $this->total_amount) * 100, 2);
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if the order can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['paid', 'completed']);
    }
}
