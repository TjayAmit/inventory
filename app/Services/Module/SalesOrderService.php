<?php

namespace App\Services\Module;

use App\DTOs\SalesOrder\SalesOrderData;
use App\Models\SalesOrder;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SalesOrderService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return SalesOrderData::class;
    }

    protected function getModelClass(): string
    {
        return SalesOrder::class;
    }

    public function getModuleName(): string
    {
        return 'sales_order';
    }

    // Sales order-specific business logic methods
    public function getActiveOrders()
    {
        return $this->repository->where('status', '!=', 'cancelled')->get();
    }

    public function getPendingOrders()
    {
        return $this->repository->where('status', 'pending')->get();
    }

    public function getConfirmedOrders()
    {
        return $this->repository->where('status', 'confirmed')->get();
    }

    public function getPaidOrders()
    {
        return $this->repository->where('payment_status', 'paid')->get();
    }

    public function getCompletedOrders()
    {
        return $this->repository->where('status', 'completed')->get();
    }

    public function getCancelledOrders()
    {
        return $this->repository->where('status', 'cancelled')->get();
    }

    public function getRefundedOrders()
    {
        return $this->repository->where('status', 'refunded')->get();
    }

    public function getOrdersByCustomer(int $customerId)
    {
        return $this->repository->where('customer_id', $customerId)->get();
    }

    public function getOrdersByBranch(int $branchId)
    {
        return $this->repository->where('branch_id', $branchId)->get();
    }

    public function getOrdersByCashier(int $cashierId)
    {
        return $this->repository->where('cashier_id', $cashierId)->get();
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

    public function getOrdersBySubtotalRange(float $minSubtotal, float $maxSubtotal)
    {
        return $this->repository->whereBetween('subtotal', [$minSubtotal, $maxSubtotal])->get();
    }

    public function getOrdersByTaxAmountRange(float $minTaxAmount, float $maxTaxAmount)
    {
        return $this->repository->whereBetween('tax_amount', [$minTaxAmount, $maxTaxAmount])->get();
    }

    public function getOrdersByShippingCostRange(float $minShippingCost, float $maxShippingCost)
    {
        return $this->repository->whereBetween('shipping_amount', [$minShippingCost, $maxShippingCost])->get();
    }

    public function getOrdersByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }

    public function getOrdersCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveOrdersCount(): int
    {
        return $this->repository->where('status', '!=', 'cancelled')->count();
    }

    public function getPendingOrdersCount(): int
    {
        return $this->repository->where('status', 'pending')->count();
    }

    public function getConfirmedOrdersCount(): int
    {
        return $this->repository->where('status', 'confirmed')->count();
    }

    public function getPaidOrdersCount(): int
    {
        return $this->repository->where('payment_status', 'paid')->count();
    }

    public function getCompletedOrdersCount(): int
    {
        return $this->repository->where('status', 'completed')->count();
    }

    public function getCancelledOrdersCount(): int
    {
        return $this->repository->where('status', 'cancelled')->count();
    }

    public function getRefundedOrdersCount(): int
    {
        return $this->repository->where('status', 'refunded')->get();
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

    public function getOrdersByCustomerAndDateRange(int $customerId, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
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

    public function getOrdersByTotalAmount(float $totalAmount)
    {
        return $this->repository->where('total_amount', $totalAmount)->get();
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
        return $this->repository->where('shipping_amount', $shippingCost)->get();
    }

    public function getOrdersByShippingCostRange(float $minShippingCost, float $maxShippingCost)
    {
        return $this->repository->whereBetween('shipping_amount', [$minShippingCost, $maxShippingCost])->get();
    }

    public function getOrdersByDiscountAmount(float $discountAmount)
    {
        return $this->repository->where('discount_amount', $discountAmount)->get();
    }

    public function getOrdersByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }

    public function getOrdersByCreator(int $createdBy)
    {
        return $this->repository->where('created_by', $createdBy)->get();
    }

    public function getOrdersByOrderNumber(string $orderNumber)
    {
        return $this->repository->where('order_number', $orderNumber)->get();
    }

    public function getOrdersByCustomerReference(string $customerReference)
    {
        return $this->repository->where('customer_reference', 'LIKE', "%{$customerReference}%")->get();
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
            $query->whereColumn('quantity', '<', 'quantity_received');
        })->get();
    }

    public function getOrdersWithItems()
    {
        return $this->repository->with(['items', 'customer', 'branch'])->get();
    }

    public function getOrdersWithItemsCount()
    {
        return $this->repository->with(['items'])
            ->selectRaw('sales_orders.*, COUNT(items.id) as items_count')
            ->groupBy('sales_orders.id')
            ->get();
    }

    public function getOrdersWithItemsAndCustomer()
    {
        return $this->repository->with(['items', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranch()
    {
        return $this->repository->with(['items', 'branch'])->get();
    }

    public function getOrdersWithItemsCustomerAndBranch()
    {
        return $this->repository->with(['items', 'customer', 'branch'])->get();
    }

    public function getOrdersWithPayments()
    {
        return $this->repository->with(['payments'])->get();
    }

    public function getOrdersWithItemsAndPayments()
    {
        return $this->repository->with(['items', 'payments'])->get();
    }

    public function getOrdersWithItemsAndCustomerAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranchAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'branch'])->get();
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

    public function isOrderNumberUnique(string $orderNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('order_number', $orderNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getOrdersByOrderType(string $orderType)
    {
        return $this->repository->where('order_type', $orderType)->get();
    }

    public function getSalesOrders()
    {
        return $this->repository->where('order_type', 'sale')->get();
    }

    public function getReturns()
    {
        return $this->repository->where('order_type', 'return')->get();
    }

    public function getExchanges()
    {
        return $this->repository->where('order_type', 'exchange')->get();
    }

    public function getOrdersByPaymentStatus(string $paymentStatus)
    {
        return $this->repository->where('payment_status', $paymentStatus)->get();
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
        return $this->repository->where('shipping_amount', $shippingCost)->get();
    }

    public function getOrdersByShippingCostRange(float $minShippingCost, float $maxShippingCost)
    {
        return $this->repository->whereBetween('shipping_amount', [$minShippingCost, $maxShippingCost])->get();
    }

    public function getOrdersByDiscountAmount(float $discountAmount)
    {
        return $this->repository->where('discount_amount', $discountAmount)->get();
    }

    public function getOrdersByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }

    public function getOrdersByCreator(int $createdBy)
    {
        return $this->repository->where('created_by', $createdBy)->get();
    }

    public function getOrdersByOrderNumber(string $orderNumber)
    {
        return $this->repository->where('order_number', $orderNumber)->get();
    }

    public function getOrdersByCustomerReference(string $customerReference)
    {
        return $this->repository->where('customer_reference', 'LIKE', "%{$customerReference}%")->get();
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
            $query->whereColumn('quantity', '<', 'quantity_received');
        })->get();
    }

    public function getOrdersWithItems()
    {
        return $this->repository->with(['items'])->get();
    }

    public function getOrdersWithItemsCount()
    {
        return $this->repository->with(['items'])
            ->selectRaw('sales_orders.*, COUNT(items.id) as items_count')
            ->groupBy('sales_orders.id')
            ->get();
    }

    public function getOrdersWithItemsAndCustomer()
    {
        return $this->repository->with(['items', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranch()
    {
        return $this->repository->with(['items', 'branch'])->get();
    }

    public function getOrdersWithItemsCustomerAndBranch()
    {
        return $this->repository->with(['items', 'customer', 'branch'])->get();
    }

    public function getOrdersWithPayments()
    {
        return $this->repository->with(['payments'])->get();
    }

    public function getOrdersWithItemsAndPayments()
    {
        return $this->repository->with(['items', 'payments'])->get();
    }

    public function getOrdersWithItemsAndCustomerAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranchAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'branch'])->get();
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

    public function isOrderNumberUnique(string $orderNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('order_number', $orderNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getOrdersByOrderType(string $orderType)
    {
        return $this->repository->where('order_type', $orderType)->get();
    }

    public function getSalesOrders()
    {
        return $this->repository->where('order_type', 'sale')->get();
    }

    public function getReturns()
    {
        return $this->repository->where('order_type', 'return')->get();
    }

    public function getExchanges()
    {
        return $this->repository->where('order_type', 'exchange')->get();
    }

    public function getOrdersByPaymentStatus(string $paymentStatus)
    {
        return $this->repository->where('payment_status', $paymentStatus)->get();
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
        return $this->repository->where('shipping_amount', $shippingCost)->get();
    }

    public function getOrdersByShippingCostRange(float $minShippingCost, float $maxShippingCost)
    {
        return $this->repository->whereBetween('shipping_amount', [$minShippingCost, $maxShippingCost])->get();
    }

    public function getOrdersByDiscountAmount(float $discountAmount)
    {
        return $this->repository->where('discount_amount', $discountAmount)->get();
    }

    public function getOrdersByDiscountAmountRange(float $minDiscountAmount, float $maxDiscountAmount)
    {
        return $this->repository->whereBetween('discount_amount', [$minDiscountAmount, $maxDiscountAmount])->get();
    }

    public function getOrdersByCreator(int $createdBy)
    {
        return $this->repository->where('created_by', $createdBy)->get();
    }

    public function getOrdersByOrderNumber(string $orderNumber)
    {
        return $this->repository->where('order_number', $orderNumber)->get();
    }

    public function getOrdersByCustomerReference(string $customerReference)
    {
        return $this->repository->where('customer_reference', 'LIKE', "%{$customerReference}%")->get();
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
            $query->whereColumn('quantity', '<', 'quantity_received');
        })->get();
    }

    public function getOrdersWithItems()
    {
        return $this->repository->with(['items'])->get();
    }

    public function getOrdersWithItemsCount()
    {
        return $this->repository->with(['items'])
            ->selectRaw('sales_orders.*, COUNT(items.id) as items_count')
            ->groupBy('sales_orders.id')
            ->get();
    }

    public function getOrdersWithItemsAndCustomer()
    {
        return $this->repository->with(['items', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranch()
    {
        return $this->repository->with(['items', 'branch'])->get();
    }

    public function getOrdersWithItemsCustomerAndBranch()
    {
        return $this->repository->with(['items', 'customer', 'branch'])->get();
    }

    public function getOrdersWithPayments()
    {
        return $this->repository->with(['payments'])->get();
    }

    public function getOrdersWithItemsAndPayments()
    {
        return $this->repository->with(['items', 'payments'])->get();
    }

    public function getOrdersWithItemsAndCustomerAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'customer'])->get();
    }

    public function getOrdersWithItemsAndBranchAndPayments()
    {
        return $this->repository->with(['items', 'payments', 'branch'])->get();
    }
}
