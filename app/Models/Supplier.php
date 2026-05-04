<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'supplier_code',
    'name',
    'contact_person',
    'email',
    'phone',
    'address',
    'city',
    'payment_terms',
    'is_active',
    'notes'
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
            'is_active' => 'boolean',
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
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
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
     * Get the primary email address.
     */
    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->email;
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
}
