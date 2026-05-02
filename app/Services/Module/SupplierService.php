<?php

namespace App\Services\Module;

use App\DTOs\Supplier\SupplierData;
use App\Models\Supplier;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SupplierService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return SupplierData::class;
    }

    protected function getModelClass(): string
    {
        return Supplier::class;
    }

    public function getModuleName(): string
    {
        return 'supplier';
    }

    // Supplier-specific business logic methods
    public function getActiveSuppliers()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getPreferredSuppliers()
    {
        return $this->repository->where('is_preferred', true)->get();
    }

    public function getSuppliersByCountry(string $country)
    {
        return $this->repository->where('country', $country)->get();
    }

    public function getSuppliersByState(string $state)
    {
        return $this->repository->where('state', $state)->get();
    }

    public function getSuppliersByCity(string $city)
    {
        return $this->repository->where('city', $city)->get();
    }

    public function searchSuppliers(string $searchTerm)
    {
        return $this->repository->where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->orWhere('contact_person', 'LIKE', "%{$searchTerm}%")
            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
            ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
            ->orWhere('mobile', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    public function getSuppliersByPaymentTerms(int $paymentTerms)
    {
        return $this->repository->where('payment_terms', $paymentTerms)->get();
    }

    public function getSuppliersByCreditLimitRange(float $minCredit, float $maxCredit)
    {
        return $this->repository->whereBetween('credit_limit', [$minCredit, $maxCredit])->get();
    }

    public function getSuppliersWithPurchaseOrders()
    {
        return $this->repository->with(['purchaseOrders'])->get();
    }

    public function getSuppliersWithPurchaseOrdersCount()
    {
        return $this->repository->with(['purchaseOrders'])
            ->selectRaw('suppliers.*, COUNT(purchase_orders.id) as purchase_orders_count')
            ->groupBy('suppliers.id')
            ->get();
    }

    public function getSuppliersCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveSuppliersCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getPreferredSuppliersCount(): int
    {
        return $this->repository->where('is_preferred', true)->count();
    }

    public function getSuppliersByTaxId(string $taxId)
    {
        return $this->repository->where('tax_id', $taxId)->get();
    }

    public function getSuppliersByWebsite(string $website)
    {
        return $this->repository->where('website', $website)->get();
    }

    public function getSuppliersByPhone(string $phone)
    {
        return $this->repository->where('phone', $phone)->get();
    }

    public function getSuppliersByEmail(string $email)
    {
        return $this->repository->where('email', $email)->get();
    }

    public function getSuppliersByMobile(string $mobile)
    {
        return $this->repository->where('mobile', $mobile)->get();
    }

    public function getSuppliersByContactPerson(string $contactPerson)
    {
        return $this->repository->where('contact_person', 'LIKE', "%{$contactPerson}%")->get();
    }

    public function isSupplierCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('code', $code);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isSupplierEmailUnique(string $email, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('email', $email);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isSupplierPhoneUnique(string $phone, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('phone', $phone);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function isSupplierMobileUnique(string $mobile, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('mobile', $mobile);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getSuppliersWithCreditLimit()
    {
        return $this->repository->where('credit_limit', '>', 0)->get();
    }

    public function getSuppliersWithoutCreditLimit()
    {
        return $this->repository->where('credit_limit', '<=', 0)->get();
    }

    public function getSuppliersByCreditLimit(float $creditLimit)
    {
        return $this->repository->where('credit_limit', $creditLimit)->get();
    }

    public function getSuppliersByPaymentTermsRange(int $minTerms, int $maxTerms)
    {
        return $this->repository->whereBetween('payment_terms', [$minTerms, $maxTerms])->get();
    }

    public function getSuppliersByCreditUtilization()
    {
        return $this->repository->with(['purchaseOrders'])
            ->selectRaw('suppliers.*, SUM(purchase_orders.total_amount) - SUM(purchase_orders.paid_amount) as outstanding_balance')
            ->groupBy('suppliers.id')
            ->get();
    }

    public function getSuppliersByPurchaseVolume()
    {
        return $this->repository->with(['purchaseOrders'])
            ->selectRaw('suppliers.*, COUNT(purchase_orders.id) as total_orders')
            ->groupBy('suppliers.id')
            ->get();
    }

    public function getSuppliersByLastPurchaseDate()
    {
        return $this->repository->with(['purchaseOrders'])
            ->selectRaw('suppliers.*, MAX(purchase_orders.order_date) as last_purchase_date')
            ->groupBy('suppliers.id')
            ->get();
    }

    public function getSuppliersByAverageOrderValue()
    {
        return $this->repository->with(['purchaseOrders'])
            ->selectRaw('suppliers.*, AVG(purchase_orders.total_amount) as average_order_value')
            ->groupBy('suppliers.id')
            ->get();
    }
}
