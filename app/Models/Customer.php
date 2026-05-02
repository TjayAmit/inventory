<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_code',
    'first_name',
    'last_name',
    'company_name',
    'email',
    'phone',
    'mobile',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    'birth_date',
    'tax_id',
    'credit_limit',
    'current_balance',
    'payment_terms',
    'customer_type',
    'is_active',
    'is_vip',
    'notes'
])]
#[Hidden([])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'payment_terms' => 'integer',
            'customer_type' => 'string',
            'is_active' => 'boolean',
            'is_vip' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the sales orders for the customer.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the payments for the customer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include VIP customers.
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Scope a query to only include individual customers.
     */
    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    /**
     * Scope a query to only include business customers.
     */
    public function scopeBusiness($query)
    {
        return $query->where('customer_type', 'business');
    }

    /**
     * Get the full name of the customer.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the display name (company name or full name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->getFullNameAttribute();
    }

    /**
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the primary phone number.
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->phone ?: $this->mobile;
    }

    /**
     * Get the primary email address.
     */
    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->email;
    }

    /**
     * Get the age of the customer.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * Get the formatted credit limit.
     */
    public function getFormattedCreditLimitAttribute(): string
    {
        return number_format($this->credit_limit, 2);
    }

    /**
     * Get the formatted current balance.
     */
    public function getFormattedCurrentBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Get the available credit.
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    /**
     * Check if customer has available credit.
     */
    public function hasAvailableCredit(float $amount = 0): bool
    {
        return $this->getAvailableCreditAttribute() >= $amount;
    }

    /**
     * Check if customer is over credit limit.
     */
    public function isOverCreditLimit(): bool
    {
        return $this->current_balance > $this->credit_limit;
    }

    /**
     * Get the formatted payment terms.
     */
    public function getFormattedPaymentTermsAttribute(): string
    {
        if ($this->payment_terms === 0) {
            return 'Immediate';
        }

        if ($this->payment_terms === 1) {
            return 'Net 1';
        }

        return "Net {$this->payment_terms}";
    }

    /**
     * Get the customer type label.
     */
    public function getCustomerTypeLabelAttribute(): string
    {
        return [
            'individual' => 'Individual',
            'business' => 'Business',
        ][$this->customer_type] ?? $this->customer_type;
    }

    /**
     * Get the total purchase amount.
     */
    public function getTotalPurchasesAttribute(): float
    {
        return $this->salesOrders()->sum('total_amount');
    }

    /**
     * Get the total paid amount.
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get the total outstanding amount.
     */
    public function getTotalOutstandingAttribute(): float
    {
        return $this->getTotalPurchasesAttribute() - $this->getTotalPaidAttribute();
    }
}
