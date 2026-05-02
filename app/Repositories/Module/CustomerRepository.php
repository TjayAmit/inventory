<?php

namespace App\Repositories\Module;

use App\Models\Customer;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class CustomerRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Customer $customer)
    {
        parent::__construct($customer);
    }

    public function getModuleName(): string
    {
        return 'customer';
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }
}
