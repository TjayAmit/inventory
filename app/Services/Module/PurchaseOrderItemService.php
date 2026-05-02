<?php

namespace App\Services\Module;

use App\DTOs\PurchaseOrderItem\purchaseOrderItemData;
use App\Models\PurchaseOrderItem;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItemService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return purchaseOrderItemData::class;
    }

    protected function getModelClass(): string
    {
        return PurchaseOrderItem::class;
    }

    public function getModuleName(): string
    {
        return 'purchase_order_item';
    }

    // Purchase order item-specific business logic methods
    public function getItemsByOrder(int $orderId)
    {
        return $this->repository->where('purchase_order_id', $orderId)->get();
    }

    public function getItemsByProduct(int $productId)
    {
        return $this->repository->where('product_id', $productId)->get();
    }

    public function getItemsByBatch(string $batchNumber)
    {
        return $this->repository->where('batch_number', $batchNumber)->get();
    }

    public function getPendingItems()
    {
        return $this->repository->whereColumn('quantity_received', '<', 'quantity_ordered')->get();
    }

    public function getReceivedItems()
    {
        return $this->repository->whereColumn('quantity_received', '>=', 'quantity_ordered')->get();
    }

    public function getPartiallyReceivedItems()
    {
        return $this->repository->whereColumn('quantity_received', '>', 0)
            ->whereColumn('quantity_received', '<', 'quantity_ordered')
            ->get();
    }

    public function getItemsWithProduct()
    {
        return $this->repository->with(['product'])->get();
    }

    public function getItemsWithBatch()
    {
        return $this->repository->with(['inventoryBatch'])->get();
    }

    public function getItemsWithProductAndBatch()
    {
        return $this->repository->with(['product', 'inventoryBatch'])->get();
    }

    public function getItemsCount(): int
    {
        return $this->repository->count();
    }

    public function getPendingItemsCount(): int
    {
        return $this->repository->whereColumn('quantity_received', '<', 'quantity_ordered')->count();
    }

    public function getReceivedItemsCount(): int
    {
        return $this->repository->whereColumn('quantity_received', '>=', 'quantity_ordered')->count();
    }

    public function getPartiallyReceivedItemsCount(): int
    {
        return $this->repository->whereColumn('quantity_received', '>', 0)
            ->whereColumn('quantity_received', '<', 'quantity_ordered')
            ->count();
    }

    public function getTotalOrderedQuantity(): int
    {
        return $this->repository->sum('quantity_ordered');
    }

    public function getTotalReceivedQuantity(): int
    {
        return $this->repository->sum('quantity_received');
    }

    public function getTotalRemainingQuantity(): int
    {
        return $this->repository->sumRaw('quantity_ordered - quantity_received');
    }

    public function getTotalCost(): float
    {
        return $this->repository->sum('total_cost');
    }

    public function getTotalTaxAmount(): float
    {
        return $this->repository->sum('tax_amount');
    }

    public function getTotalDiscountAmount(): float
    {
        return $this->repository->sum('discount_amount');
    }

    public function getTotalLineTotal(): float
    {
        return $this->repository->sum('line_total');
    }

    public function getItemsByUnitCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('unit_cost', [$minCost, $maxCost])->get();
    }

    public function getItemsByTotalCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('total_cost', [$minCost, $maxCost])->get();
    }

    public function getItemsByTaxRateRange(float $minTaxRate, float $maxTaxRate)
    {
        return $this->repository->whereBetween('tax_rate', [$minTaxRate, $maxTaxRate])->get();
    }

    public function getItemsByTaxAmountRange(float $minTaxAmount, float $maxTaxAmount)
    {
        return $this->repository->whereBetween('tax_amount', [$minTaxAmount, $maxTaxAmount])->get();
    }

    public function getItemsByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }

    public function getItemsByLineTotalRange(float $minLineTotal, float $maxLineTotal)
    {
        return $this->repository->whereBetween('line_total', [$minLineTotal, $maxLineTotal])->get();
    }

    public function getItemsByExpiryDate(string $expiryDate)
    {
        return $this->repository->where('expiry_date', $expiryDate)->get();
    }

    public function getItemsByExpiryDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('expiry_date', [$startDate, $endDate])->get();
    }

    public function getItemsExpiringSoon(int $days = 30)
    {
        return $this->repository->where('expiry_date', '<=', now()->addDays($days))->get();
    }

    public function getItemsWithExpiryDate()
    {
        return $this->repository->whereNotNull('expiry_date')->get();
    }

    public function getItemsWithoutExpiryDate()
    {
        return $this->repository->whereNull('expiry_date')->get();
    }

    public function getItemsByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getItemsWithNotes()
    {
        return $this->repository->whereNotNull('notes')->get();
    }

    public function updateReceivedQuantity(int $itemId, int $quantity): bool
    {
        return $this->repository->update($itemId, ['quantity_received' => $quantity]);
    }

    public function receiveItem(int $itemId, int $quantity): bool
    {
        $item = $this->repository->find($itemId);
        
        if (!$item) {
            return false;
        }

        $newQuantity = $item->quantity_received + $quantity;
        
        if ($newQuantity > $item->quantity_ordered) {
            $newQuantity = $item->quantity_ordered;
        }
        
        return $this->repository->update($itemId, ['quantity_received' => $newQuantity]);
    }

    public function isItemBatchUnique(string $batchNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('batch_number', $batchNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getItemsByOrderAndProduct(int $orderId, int $productId)
    {
        return $this->repository->where('purchase_order_id', $orderId)
            ->where('product_id', $productId)
            ->get();
    }

    public function getItemsReceivingProgress()
    {
        return $this->repository->with(['product'])
            ->selectRaw('purchase_order_items.*, (quantity_received / quantity_ordered) * 100 as receiving_percentage')
            ->get();
    }

    public function getItemsByDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getItemsByUnitCost(float $unitCost)
    {
        return $this->repository->where('unit_cost', $unitCost)->get();
    }

    public function getItemsByTaxRate(float $taxRate)
    {
        return $this->repository->where('tax_rate', $taxRate)->get();
    }

    public function getItemsByTaxAmount(float $taxAmount)
    {
        return $this->repository->where('tax_amount', $taxAmount)->get();
    }

    public function getItemsByDiscountPercent(float $discountPercent)
    {
        return $this->repository->where('discount_percent', $discountPercent)->get();
    }

    public function getItemsByDiscountAmount(float $discountAmount)
    {
        return $this->repository->where('discount_amount', $discountAmount)->get();
    }

    public function getItemsByLineTotal(float $lineTotal)
    {
        return $this->repository->where('line_total', $lineTotal)->get();
    }

    public function getItemsByTotalCost(float $totalCost)
    {
        return $this->repository->where('total_cost', $totalCost)->get();
    }

    public function getItemsByCreatedDate(string $createdDate)
    {
        return $this->repository->whereDate('created_at', $createdDate)->get();
    }

    public function getItemsByCreatedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getItemsByExpiryDate(string $expiryDate)
    {
        return $this->repository->whereDate('expiry_date', $expiryDate)->get();
    }

    public function getItemsByExpiryDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('expiry_date', [$startDate, $endDate])->get();
    }

    public function getItemsBySupplierReference(string $supplierReference)
    {
        return $this->repository->where('supplier_reference', 'LIKE', "%{$supplierReference}%")->get();
    }

    public function getItemsBySupplierReferenceExact(string $supplierReference)
    {
        return $this->repository->where('supplier_reference', $supplierReference)->get();
    }

    public function getItemsByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getItemsWithNotes()
    {
        return $this->repository->whereNotNull('notes')->get();
    }

    public function getItemsWithoutNotes()
    {
        return $this->repository->whereNull('notes')->get();
    }
}
