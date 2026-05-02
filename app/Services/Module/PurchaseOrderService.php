<?php

namespace App\Services\Module;

use App\DTOs\PurchaseOrder\PurchaseOrderData;
use App\Models\PurchaseOrder;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return PurchaseOrderData::class;
    }

    protected function getModelClass(): string
    {
        return PurchaseOrder::class;
    }

    public function getModuleName(): string
    {
        return 'purchase_order';
    }

    // Purchase order-specific business logic methods
    public function getActiveOrders()
    {
        return $this->repository->where('status', '!=', 'cancelled')->get();
    }

    public function getDraftOrders()
    {
        return $this->repository->where('status', 'draft')->get();
    }

    public function getSentOrders()
    {
        return $this->repository->where('status', 'sent')->get();
    }

    public function getConfirmedOrders()
    {
        return $this->repository->where('status', 'confirmed')->get();
    }

    public function getReceivedOrders()
    {
        return $this->repository->where('status', 'received')->get();
    }

    public function getPendingApprovalOrders()
    {
        return $this->repository->where('status', 'pending_approval')->get();
    }

    public function getOrdersBySupplier(int $supplierId)
    {
        return $this->repository->where('supplier_id', $supplierId)->get();
    }

    public function getOrdersByBranch(int $branchId)
    {
        return $this->repository->where('branch_id', $branchId)->get();
    }

    public function getOrdersByDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('order_date', [$startDate, $endDate])->get();
    }

    public function getOrdersByStatus(string $status)
    {
        return $this->repository->where('status', $status)->get();
    }

    public function getOrdersByTotalAmountRange(float $minAmount, float $maxAmount)
    {
        return $this->repository->whereBetween('total_amount', [$minAmount, $maxAmount])->get();
    }

    public function getOverdueOrders()
    {
        return $this->repository->where('expected_date', '<', now())
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'received')
            ->get();
    }

    public function getOrdersWithItems()
    {
        return $this->repository->with(['items', 'supplier', 'branch'])->get();
    }

    public function getOrdersCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveOrdersCount(): int
    {
        return $this->repository->where('status', '!=', 'cancelled')->count();
    }

    public function getDraftOrdersCount(): int
    {
        return $this->repository->where('status', 'draft')->count();
    }

    public function getSentOrdersCount(): int
    {
        return $this->repository->where('status', 'sent')->count();
    }

    public function getConfirmedOrdersCount(): int
    {
        return $this->repository->where('status', 'confirmed')->count();
    }

    public function getReceivedOrdersCount(): int
    {
        return $this->repository->where('status', 'received')->count();
    }

    public function getPendingApprovalOrdersCount(): int
    {
        return $this->repository->where('status', 'pending_approval')->count();
    }

    public function getCancelledOrdersCount(): int
    {
        return $this->repository->where('status', 'cancelled')->count();
    }

    public function getTotalOrderValue(): float
    {
        return $this->repository->sum('total_amount');
    }

    public function getTotalPaidAmount(): float
    {
        return $this->repository->sum('paid_amount');
    }

    public function getTotalOutstandingAmount(): float
    {
        return $this->repository->sumRaw('total_amount - paid_amount');
    }

    public function getOrdersBySupplierAndDateRange(int $supplierId, string $startDate, string $endDate)
    {
        return $this->repository->where('supplier_id', $supplierId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->get();
    }

    public function getOrdersByBranchAndDateRange(int $branchId, string $startDate, string $endDate)
    {
        return $this->repository->where('branch_id', $branchId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->get();
    }

    public function getOrdersByStatusAndDateRange(string $status, string $startDate, string $endDate)
    {
        return $this->repository->where('status', $status)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->get();
    }

    public function getOrdersByCompletionPercentage()
    {
        return $this->repository->with(['items'])
            ->selectRaw('purchase_orders.*, (SUM(CASE WHEN purchase_order_items.quantity_received >= purchase_order_items.quantity_ordered THEN 1 ELSE 0 END) / COUNT(*)) * 100) as completion_percentage')
            ->groupBy('purchase_orders.id')
            ->get();
    }

    public function getOrdersWithSupplier()
    {
        return $this->repository->with(['supplier'])->get();
    }

    public function getOrdersWithBranch()
    {
        return $this->repository->with(['branch'])->get();
    }

    public function getOrdersWithItemsAndSupplier()
    {
        return $this->repository->with(['items', 'supplier'])->get();
    }

    public function getOrdersWithItemsAndBranch()
    {
        return $this->repository->with(['items', 'branch'])->get();
    }

    public function getOrdersWithItemsSupplierAndBranch()
    {
        return $this->repository->with(['items', 'supplier', 'branch'])->get();
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->repository->update($orderId, ['status' => $status]);
    }

    public function approveOrder(int $orderId, int $approvedBy): bool
    {
        return $this->repository->update($orderId, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function cancelOrder(int $orderId): bool
    {
        return $this->repository->update($orderId, ['status' => 'cancelled']);
    }

    public function markAsReceived(int $orderId, string $receivedDate): bool
    {
        return $this->repository->update($orderId, [
            'status' => 'received',
            'received_date' => $receivedDate,
        ]);
    }

    public function updatePaidAmount(int $orderId, float $paidAmount): bool
    {
        return $this->repository->update($orderId, ['paid_amount' => $paidAmount]);
    }

    public function isOrderNumberUnique(string $orderNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('order_number', $orderNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getOrdersBySupplierReference(string $supplierReference)
    {
        return $this->repository->where('supplier_reference', 'LIKE', "%{$supplierReference}%")->get();
    }

    public function getOrdersByInternalNotes(string $notes)
    {
        return $this->repository->where('internal_notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getOrdersByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getOrdersByExpectedDate(string $expectedDate)
    {
        return $this->repository->where('expected_date', $expectedDate)->get();
    }

    public function getOrdersByExpectedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('expected_date', [$startDate, $endDate])->get();
    }

    public function getOrdersByReceivedDate(string $receivedDate)
    {
        return $this->repository->where('received_date', $receivedDate)->get();
    }

    public function getOrdersByReceivedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('received_date', [$startDate, $endDate])->get();
    }

    public function getOrdersNeedingApproval()
    {
        return $this->repository->where('status', 'pending_approval')->get();
    }

    public function getOrdersNeedingReceiving()
    {
        return $this->repository->where('status', 'confirmed')->get();
    }

    public function getOrdersWithPartialReceiving()
    {
        return $this->repository->whereHas('items', function ($query) {
            $query->whereColumn('quantity_received', '<', 'quantity_ordered');
        })->get();
    }

    public function getOrdersByCreator(int $createdBy)
    {
        return $this->repository->where('created_by', $createdBy)->get();
    }

    public function getOrdersByTotalAmount(float $totalAmount)
    {
        return $this->repository->where('total_amount', $totalAmount)->get();
    }

    public function getOrdersByTotalAmountRange(float $minAmount, float $maxAmount)
    {
        return $this->repository->whereBetween('total_amount', [$minAmount, $maxAmount])->get();
    }

    public function getOrdersBySubtotal(float $subtotal)
    {
        return $this->repository->where('subtotal', $subtotal)->get();
    }

    public function getOrdersBySubtotalRange(float $minSubtotal, float $maxSubtotal)
    {
        return $this->repository->whereBetween('subtotal', [$minSubtotal, $maxSubtotal])->get();
    }

    public function getOrdersByTaxAmount(float $taxAmount)
    {
        return $this->repository->where('tax_amount', $taxAmount)->get();
    }

    public function getOrdersByTaxAmountRange(float $minTaxAmount, float $maxTaxAmount)
    {
        return $this->repository->whereBetween('tax_amount', [$minTaxAmount, $maxTaxAmount])->get();
    }

    public function getOrdersByShippingCost(float $shippingCost)
    {
        return $this->repository->where('shipping_cost', $shippingCost)->get();
    }

    public function getOrdersByShippingCostRange(float $minShippingCost, float $maxShippingCost)
    {
        return $this->repository->whereBetween('shipping_cost', [$minShippingCost, $maxShippingCost])->get();
    }

    public function getOrdersByDiscountAmount(float $discountAmount)
    {
        return $this->repository->where('discount_amount', $discountAmount)->get();
    }

    public function getOrdersByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }
}
