<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code',
    'name',
    'contact_person',
    'email',
    'phone',
    'mobile',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    'tax_id',
    'website',
    'notes',
    'credit_limit',
    'payment_terms',
    'is_active',
    'is_preferred'
])]
#[Hidden([])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'payment_terms' => 'integer',
            'is_active' => 'boolean',
            'is_preferred' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the purchase orders for the supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include preferred suppliers.
     */
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
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
     * Get the primary contact information.
     */
    public function getPrimaryContactAttribute(): string
    {
        return $this->contact_person ?: $this->name;
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
     * Check if supplier has credit limit.
     */
    public function hasCreditLimit(): bool
    {
        return $this->credit_limit > 0;
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
     * Check if supplier is within credit limit for a given amount.
     */
    public function isWithinCreditLimit(float $amount): bool
    {
        if (!$this->hasCreditLimit()) {
            return true;
        }

        $currentBalance = $this->purchaseOrders()
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') - $this->purchaseOrders()
            ->where('status', '!=', 'cancelled')
            ->sum('paid_amount');

        return ($currentBalance + $amount) <= $this->credit_limit;
    }
}
