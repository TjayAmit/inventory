<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'payment_number',
    'sales_order_id',
    'customer_id',
    'received_by',
    'payment_method',
    'status',
    'amount',
    'refunded_amount',
    'currency',
    'exchange_rate',
    'card_type',
    'card_last_four',
    'transaction_id',
    'authorization_code',
    'check_number',
    'bank_name',
    'account_number',
    'notes',
    'payment_date'
])]
#[Hidden([])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'payment_date' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the sales order that owns the payment.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the customer that owns the payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user that received the payment.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to only include payments with specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include refunded payments.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope a query to only include cash payments.
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    /**
     * Scope a query to only include card payments.
     */
    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    /**
     * Scope a query to only include check payments.
     */
    public function scopeCheck($query)
    {
        return $query->where('payment_method', 'check');
    }

    /**
     * Scope a query to only include bank transfer payments.
     */
    public function scopeBankTransfer($query)
    {
        return $query->where('payment_method', 'bank_transfer');
    }

    /**
     * Get the net amount after refunds.
     */
    public function getNetAmountAttribute(): float
    {
        return $this->amount - $this->refunded_amount;
    }

    /**
     * Check if the payment is fully refunded.
     */
    public function isFullyRefunded(): bool
    {
        return $this->refunded_amount >= $this->amount;
    }

    /**
     * Check if the payment is partially refunded.
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->refunded_amount > 0 && $this->refunded_amount < $this->amount;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'check' => 'Check',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'credit' => 'Credit',
            'other' => 'Other',
        ][$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get the formatted refunded amount.
     */
    public function getFormattedRefundedAmountAttribute(): string
    {
        return number_format($this->refunded_amount, 2);
    }

    /**
     * Get the formatted net amount.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return number_format($this->getNetAmountAttribute(), 2);
    }

    /**
     * Get the formatted exchange rate.
     */
    public function getFormattedExchangeRateAttribute(): string
    {
        return number_format($this->exchange_rate, 6);
    }

    /**
     * Check if the payment is a card payment.
     */
    public function isCardPayment(): bool
    {
        return in_array($this->payment_method, ['card']);
    }

    /**
     * Check if the payment is a check payment.
     */
    public function isCheckPayment(): bool
    {
        return in_array($this->payment_method, ['check']);
    }

    /**
     * Check if the payment is a bank transfer.
     */
    public function isBankTransferPayment(): bool
    {
        return in_array($this->payment_method, ['bank_transfer']);
    }

    /**
     * Get the masked card number.
     */
    public function getMaskedCardNumberAttribute(): ?string
    {
        if (!$this->card_last_four) {
            return null;
        }

        return '**** **** **** ' . $this->card_last_four;
    }

    /**
     * Get the masked account number.
     */
    public function getMaskedAccountNumberAttribute(): ?string
    {
        if (!$this->account_number || strlen($this->account_number) < 4) {
            return null;
        }

        $length = strlen($this->account_number);
        $lastFour = substr($this->account_number, -4);
        
        return str_repeat('*', $length - 4) . $lastFour;
    }

    /**
     * Check if the payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['completed']) && !$this->isFullyRefunded();
    }

    /**
     * Get the refundable amount.
     */
    public function getRefundableAmountAttribute(): float
    {
        return max(0, $this->getNetAmountAttribute());
    }

    /**
     * Scope a query to only include payments within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include payments for a specific customer.
     */
    public function scopeCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include payments received by a specific user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('received_by', $userId);
    }
}
