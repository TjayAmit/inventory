<?php

namespace App\Services\Module;

use App\DTOs\Payment\PaymentData;
use App\Models\Payment;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PaymentService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return PaymentData::class;
    }

    protected function getModelClass(): string
    {
        return Payment::class;
    }

    public function getModuleName(): string
    {
        return 'payment';
    }

    // Payment-specific business logic methods
    public function getActivePayments()
    {
        return $this->repository->where('status', '!=', 'cancelled')->get();
    }

    public function getCompletedPayments()
    {
        return $this->repository->where('status', 'completed')->get();
    }

    public function getPendingPayments()
    {
        return $this->repository->where('status', 'pending')->get();
    }

    public function getFailedPayments()
    {
        return $this->repository->where('status', 'failed')->get();
    }

    public function getRefundedPayments()
    {
        return $this->repository->where('status', 'refunded')->get();
    }

    public function getPaymentsBySalesOrder(int $salesOrderId)
    {
        return $this->repository->where('sales_order_id', $salesOrderId)->get();
    }

    public function getPaymentsByCustomer(int $customerId)
    {
        return $this->repository->where('customer_id', $customerId)->get();
    }

    public function getPaymentsByReceivedBy(int $receivedBy)
    {
        return $this->repository->where('received_by', $receivedBy)->get();
    }

    public function getPaymentsByPaymentMethod(string $paymentMethod)
    {
        return $this->repository->where('payment_method', $paymentMethod)->get();
    }

    public function getCashPayments()
    {
        return $this->repository->where('payment_method', 'cash')->get();
    }

    public function getCardPayments()
    {
        return $this->repository->where('payment_method', 'card')->get();
    }

    public function getCheckPayments()
    {
        return $this->repository->where('payment_method', 'check')->get();
    }

    public function getBankTransferPayments()
    {
        return $this->repository->where('payment_method', 'bank_transfer')->get();
    }

    public function getMobileMoneyPayments()
    {
        return $this->repository->where('payment_method', 'mobile_money')->get();
    }

    public function getCreditPayments()
    {
        return $this->repository->where('payment_method', 'credit')->get();
    }

    public function getOtherPayments()
    {
        return $this->repository->where('payment_method', 'other')->get();
    }

    public function getPaymentsByDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('payment_date', [$startDate, $endDate])->get();
    }

    public function getPaymentsByAmountRange(float $minAmount, float $maxAmount)
    {
        return $this->repository->whereBetween('amount', [$minAmount, $maxAmount])->get();
    }

    public function getPaymentsCount(): int
    {
        return $this->repository->count();
    }

    public function getActivePaymentsCount(): int
    {
        return $this->repository->where('status', '!=', 'cancelled')->count();
    }

    public function getCompletedPaymentsCount(): int
    {
        return $this->repository->where('status', 'completed')->count();
    }

    public function getPendingPaymentsCount(): int
    {
        return $this->repository->where('status', 'pending')->count();
    }

    public function getFailedPaymentsCount(): int
    {
        return $this->repository->where('status', 'failed')->count();
    }

    public function getRefundedPaymentsCount(): int
    {
        return $this->repository->where('status', 'refunded')->count();
    }

    public function getTotalPaidAmount(): float
    {
        return $this->repository->sum('amount');
    }

    public function getTotalRefundedAmount(): float
    {
        return $this->repository->sum('refunded_amount');
    }

    public function getTotalNetAmount(): float
    {
        return $this->repository->sumRaw('amount - refunded_amount');
    }

    public function getPaymentsByCurrency(string $currency)
    {
        return $this->repository->where('currency', $currency)->get();
    }

    public function getPaymentsByExchangeRate(float $exchangeRate)
    {
        return $this->repository->where('exchange_rate', $exchangeRate)->get();
    }

    public function getPaymentsByCardType(string $cardType)
    {
        return $this->repository->where('card_type', $cardType)->get();
    }

    public function getPaymentsByCardLastFour(string $cardLastFour)
    {
        return $this->repository->where('card_last_four', $cardLastFour)->get();
    }

    public function getPaymentsByTransactionId(string $transactionId)
    {
        return $this->repository->where('transaction_id', $transactionId)->get();
    }

    public function getPaymentsByAuthorizationCode(string $authorizationCode)
    {
        return $this->repository->where('authorization_code', $authorizationCode)->get();
    }

    public function getPaymentsByCheckNumber(string $checkNumber)
    {
        return $this->repository->where('check_number', $checkNumber)->get();
    }

    public function getPaymentsByBankName(string $bankName)
    {
        return $this->repository->where('bank_name', $bankName)->get();
    }

    public function getPaymentsByAccountNumber(string $accountNumber)
    {
        return $this->repository->where('account_number', $accountNumber)->get();
    }

    public function getPaymentsByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getPaymentsWithNotes()
    {
        return $this->repository->whereNotNull('notes')->get();
    }

    public function getPaymentsWithoutNotes()
    {
        return $this->repository->whereNull('notes')->get();
    }

    public function getPaymentsByPaymentDate(string $paymentDate)
    {
        return $this->repository->where('payment_date', $paymentDate)->get();
    }

    public function getPaymentsByPaymentDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('payment_date', [$startDate, $endDate])->get();
    }

    public function getPaymentsByCustomerAndDateRange(int $customerId, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsBySalesOrderAndDateRange(int $salesOrderId, string $startDate, string $endDate)
    {
        return $this->repository->where('sales_order_id', $salesOrderId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByAmount(float $amount)
    {
        return $this->repository->where('amount', $amount)->get();
    }

    public function getPaymentsByAmountRange(float $minAmount, float $maxAmount)
    {
        return $this->repository->whereBetween('amount', [$minAmount, $maxAmount])->get();
    }

    public function getPaymentsByRefundedAmount(float $refundedAmount)
    {
        return $this->repository->where('refunded_amount', $refundedAmount)->get();
    }

    public function getPaymentsByNetAmountRange(float $minNetAmount, float $maxNetAmount)
    {
        return $this->repository->whereRaw('(amount - refunded_amount) BETWEEN ? AND ?', [$minNetAmount, $maxNetAmount])->get();
    }

    public function updatePaymentStatus(int $paymentId, string $status): bool
    {
        return $this->repository->update($paymentId, ['status' => $status]);
    }

    public function refundPayment(int $paymentId, float $refundAmount): bool
    {
        return $this->repository->update($paymentId, [
            'status' => 'refunded',
            'refunded_amount' => $refundAmount,
        ]);
    }

    public function isPaymentNumberUnique(string $paymentNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('payment_number', $paymentNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isTransactionIdUnique(string $transactionId, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('transaction_id', $transactionId);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isAuthorizationCodeUnique(string $authorizationCode, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('authorization_code', $authorizationCode);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isCheckNumberUnique(string $checkNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('check_number', $checkNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isAccountNumberUnique(string $accountNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('account_number', $accountNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getPaymentsByCustomerAndDateRange(int $customerId, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsBySalesOrderAndDateRange(int $salesOrderId, string $startDate, string $endDate)
    {
        return $this->repository->where('sales_order_id', $salesOrderId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByPaymentMethodAndDateRange(string $paymentMethod, string $startDate, string $endDate)
    {
        return $this->repository->where('payment_method', $paymentMethod)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethod(int $customerId, string $paymentMethod)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->get();
    }

    public function getPaymentsWithSalesOrders()
    {
        return $this->repository->with(['salesOrder'])->get();
    }

    public function getPaymentsWithSalesOrdersCount()
    {
        return $this->repository->with(['salesOrder'])
            ->selectRaw('payments.*, COUNT(sales_orders.id) as sales_orders_count')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsWithCustomers()
    {
        return $this->repository->with(['customer'])->get();
    }

    public function getPaymentsWithCustomersCount()
    {
        return $this->repository->with(['customer'])
            ->selectRaw('payments.*, COUNT(customers.id) as customers_count')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsWithSalesOrdersAndCustomers()
    {
        return $this->repository->with(['salesOrder', 'customer'])->get();
    }

    public function getPaymentsByLastActivityDate()
    {
        return $this->repository->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsByCustomerAndLastActivityDate(int $customerId)
    {
        return $this->repository->where('customer_id', $customerId)
            ->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsByAmountRangeAndDateRange(float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByPaymentMethodAndAmountRange(string $paymentMethod, float $minAmount, float $maxAmount)
    {
        return $this->repository->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->get();
    }

    public function getPaymentsByCustomerAndAmountRange(int $customerId, float $minAmount, float $maxAmount)
    {
        return $this->repository->where('customer_id', $customerId)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->get();
    }

    public function getPaymentsByCustomerAndDateRangeAndAmountRange(int $customerId, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndAmountRange(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndDateRange(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndAmountRange(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByCustomerAndLastActivityDate(int $customerId)
    {
        return $this->repository->where('customer_id', $customerId)
            ->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndLastActivityDate(int $customerId, string $paymentMethod)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndAmountRange(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndAmountRangeAndLastActivityDate(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }

    public function getPaymentsByCustomerAndPaymentMethodAndAmountRangeAndLastActivityDate(int $customerId, string $paymentMethod, float $minAmount, float $maxAmount, string $startDate, string $endDate)
    {
        return $this->repository->where('customer_id', $customerId)
            ->where('payment_method', $paymentMethod)
            ->whereBetween('amount', [$minAmount, $maxAmount])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->with(['salesOrder'])
            ->selectRaw('payments.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('payments.id')
            ->get();
    }
}
