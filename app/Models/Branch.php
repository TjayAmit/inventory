<?php

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code',
    'name',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    'phone',
    'email',
    'manager_id',
    'is_active',
    'is_main_branch',
    'timezone',
    'currency',
    'tax_rate',
    'operating_hours'
])]
#[Hidden([])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
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
            'is_main_branch' => 'boolean',
            'tax_rate' => 'decimal:4',
            'operating_hours' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the manager that owns the branch.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the inventory records for the branch.
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the purchase orders for the branch.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the sales orders for the branch.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the users assigned to the branch.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the inventory adjustments for the branch.
     */
    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /**
     * Get the inventory transfers from the branch.
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'from_branch_id');
    }

    /**
     * Get the inventory transfers to the branch.
     */
    public function transfersTo(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'to_branch_id');
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include main branches.
     */
    public function scopeMain($query)
    {
        return $query->where('is_main_branch', true);
    }
}
