<?php

namespace App\Services\Module;

use App\DTOs\Customer\CustomerData;
use App\Models\Customer;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return CustomerData::class;
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }

    public function getModuleName(): string
    {
        return 'customer';
    }

    // Customer-specific business logic methods
    public function getActiveCustomers()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getVipCustomers()
    {
        return $this->repository->where('is_vip', true)->get();
    }

    public function getIndividualCustomers()
    {
        return $this->repository->where('customer_type', 'individual')->get();
    }

    public function getBusinessCustomers()
    {
        return $this->repository->where('customer_type', 'business')->get();
    }

    public function getCustomersByCountry(string $country)
    {
        return $this->repository->where('country', $country)->get();
    }

    public function getCustomersByState(string $state)
    {
        return $this->repository->where('state', $state)->get();
    }

    public function getCustomersByCity(string $city)
    {
        return $this->repository->where('city', $city)->get();
    }

    public function searchCustomers(string $searchTerm)
    {
        return $this->repository->where('first_name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('company_name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('customer_code', 'LIKE', "%{$searchTerm}%")
            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
            ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
            ->orWhere('mobile', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    public function getCustomersByCreditLimitRange(float $minCredit, float $maxCredit)
    {
        return $this->repository->whereBetween('credit_limit', [$minCredit, $maxCredit])->get();
    }

    public function getCustomersByPaymentTerms(int $paymentTerms)
    {
        return $this->repository->where('payment_terms', $paymentTerms)->get();
    }

    public function getCustomersByBirthDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('birth_date', [$startDate, $endDate])->get();
    }

    public function getCustomersByTaxId(string $taxId)
    {
        return $this->repository->where('tax_id', $taxId)->get();
    }

    public function getCustomersByWebsite(string $website)
    {
        return $this->repository->where('website', $website)->get();
    }

    public function getCustomersByPhone(string $phone)
    {
        return $this->repository->where('phone', $phone)->get();
    }

    public function getCustomersByMobile(string $mobile)
    {
        return $this->repository->where('mobile', $mobile)->get();
    }

    public function getCustomersByEmail(string $email)
    {
        return $this->repository->where('email', $email)->get();
    }

    public function getCustomersByContactPerson(string $contactPerson)
    {
        return $this->repository->where('contact_person', 'LIKE', "%{$contactPerson}%")->get();
    }

    public function getCustomersCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveCustomersCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getVipCustomersCount(): int
    {
        return $this->repository->where('is_vip', true)->count();
    }

    public function getIndividualCustomersCount(): int
    {
        return $this->repository->where('customer_type', 'individual')->count();
    }

    public function getBusinessCustomersCount(): int
    {
        return $this->repository->where('customer_type', 'business')->count();
    }

    public function getCustomersByCreditLimit(float $creditLimit)
    {
        return $this->repository->where('credit_limit', $creditLimit)->get();
    }

    public function getCustomersWithoutCreditLimit()
    {
        return $this->repository->where('credit_limit', '<=', 0)->get();
    }

    public function getCustomersByCreditUtilization()
    {
        return $this->repository->with(['salesOrders'])->get();
    }

    public function getCustomersByCreditUtilizationPercentage()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, (SUM(sales_orders.total_amount) / customers.credit_limit) * 100) as credit_utilization_percentage')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByLastPurchaseDate()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, MAX(sales_orders.order_date) as last_purchase_date')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByPurchaseVolume()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, COUNT(sales_orders.id) as total_orders')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByAverageOrderValue()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, AVG(sales_orders.total_amount) as average_order_value')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByTotalPurchases()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, SUM(sales_orders.total_amount) as total_purchases')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByTotalOutstanding()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, (SUM(sales_orders.total_amount) - SUM(payments.amount)) as total_outstanding')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByAgeRange(int $minAge, int $maxAge)
    {
        return $this->repository->whereRaw('TIMESTAMPDIFF(YEAR, CURDATE(), birth_date) BETWEEN ? AND ?', [$minAge, $maxAge])->get();
    }

    public function getCustomersByRegistrationDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function isCustomerCodeUnique(string $customerCode, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('customer_code', $customerCode);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isCustomerEmailUnique(string $email, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('email', $email);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isCustomerPhoneUnique(string $phone, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('phone', $phone);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isCustomerMobileUnique(string $mobile, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('mobile', $mobile);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getCustomersByLastActivity()
    {
        return $this->repository->with(['salesOrders', 'payments'])
            ->selectRaw('customers.*, GREATEST(COALESCE(MAX(sales_orders.order_date), MAX(payments.payment_date))) as last_activity_date')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersWithSalesOrders()
    {
        return $this->repository->with(['salesOrders'])->get();
    }

    public function getCustomersWithPayments()
    {
        return $this->repository->with(['payments'])->get();
    }

    public function getCustomersWithSalesOrdersAndPayments()
    {
        return $this->repository->with(['salesOrders', 'payments'])->get();
    }

    public function getCustomersByTotalPurchaseAmount(float $totalAmount)
    {
        return $this->repository->whereHas('salesOrders', function ($query) {
            $query->havingRaw('SUM(sales_orders.total_amount) >= ?', [$totalAmount]);
        })->get();
    }

    public function getCustomersByTotalPurchaseAmountRange(float $minAmount, float $maxAmount)
    {
        return $this->repository->whereHas('salesOrders', function ($query) {
            $query->havingRaw('SUM(sales_orders.total_amount) BETWEEN ? AND ?', [$minAmount, $maxAmount]);
        })->get();
    }

    public function getCustomersByTotalPurchaseCount(int $minCount, int $maxCount)
    {
        return $this->repository->whereHas('salesOrders', function ($query) {
            $query->havingRaw('COUNT(sales_orders.id) BETWEEN ? AND ?', [$minCount, $maxCount]);
        })->get();
    }

    public function getCustomersByAveragePurchaseValue()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, AVG(sales_orders.total_amount) as average_purchase_value')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByPaymentTermsRange(int $minTerms, int $maxTerms)
    {
        return $this->repository->whereBetween('payment_terms', [$minTerms, $maxTerms])->get();
    }

    public function getCustomersByRecency()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, DATEDIFF(DAY, CURDATE(), MAX(sales_orders.order_date)) as days_since_last_purchase')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByLifetimeValue()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, SUM(sales_orders.total_amount) as lifetime_value')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getCustomersWithNotes()
    {
        return $this->repository->whereNotNull('notes')->get();
    }

    public function getCustomersWithoutNotes()
    {
        return $this->repository->whereNull('notes')->get();
    }

    public function getCustomersByBirthDate(string $birthDate)
    {
        return $this->repository->where('birth_date', $birthDate)->get();
    }

    public function getCustomersByAge(int $age)
    {
        return $this->repository->whereRaw('TIMESTAMPDIFF(YEAR, CURDATE(), birth_date) = ?', [$age])->get();
    }

    public function getCustomersByPostalCode(string $postalCode)
    {
        return $this->repository->where('postal_code', $postalCode)->get();
    }

    public function getCustomersByPostalCodeRange(string $startCode, string $endCode)
    {
        return $this->repository->whereRaw('postal_code REGEXP ?', ["^{$startCode}.*{$endCode}$"])->get();
    }

    public function getCustomersByAddress(string $address)
    {
        return $this->repository->where('address', 'LIKE', "%{$address}%")->get();
    }

    public function getCustomersByAddressComponent(string $component, string $value)
    {
        return $this->repository->where('address', 'LIKE', "%{$component}%")->get();
    }

    public function getCustomersByFullAddress(string $fullAddress)
    {
        return $this->repository->where('address', $fullAddress)->get();
    }

    public function getCustomersByCompanyDomain(string $domain)
    {
        return $this->repository->where('email', 'LIKE', "%@{$domain}%")->get();
    }

    public function getCustomersByDisposableEmail()
    {
        return $this->repository->where(function ($query) {
            $query->where('email', 'REGEXP', '^[^@]+@(gmail|yahoo|hotmail|outlook|aol|mail|icloud|protonmail|yandex|qq|163|126|sina|sohu|tom|excite|inbox|gmx|mail|com|live|co|email|eu|msn|aim|mac|me|rocketmail|fastmail|yopmail|hey|com)$');
        })->get();
    }

    public function getCustomersByLoyaltyStatus()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, CASE WHEN COUNT(sales_orders.id) >= 10 THEN \'Gold\' WHEN COUNT(sales_orders.id) >= 5 THEN \'Silver\' ELSE \'Bronze\' END as loyalty_status')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByPurchaseRecency()
    {
        return $this->repository->with(['salesOrders'])
            ->selectRaw('customers.*, CASE WHEN DATEDIFF(DAY, CURDATE(), MAX(sales_orders.order_date)) <= 30 THEN \'Recent\' WHEN DATEDIFF(DAY, CURDATE(), MAX(sales_orders.order_date)) <= 90 THEN \'Occasional\' ELSE \'Inactive\' END as purchase_recency')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByPreferredCommunicationMethod()
    {
        return $this->repository->with(['salesOrders', 'payments'])
            ->selectRaw('customers.*, CASE WHEN COUNT(sales_orders.id) > COUNT(payments.id) THEN \'Email\' WHEN COUNT(sales_orders.id) <= COUNT(payments.id) THEN \'Phone\' ELSE \'Mixed\' END as preferred_communication')
            ->groupBy('customers.id')
            ->get();
    }

    public function getCustomersByPreferredContactMethod()
    {
        return $this->repository->with(['salesOrders', 'payments'])
            ->selectRaw('customers.*, CASE WHEN COUNT(sales_orders.id) > COUNT(payments.id) THEN \'In-Person\' WHEN COUNT(sales_orders.id) <= COUNT(payments.id) THEN \'Online\' ELSE \'Mixed\' END as preferred_contact_method')
            ->groupBy('customers.id')
            ->get();
    }
}
