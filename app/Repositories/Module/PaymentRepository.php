<?php

namespace App\Repositories\Module;

use App\Models\Payment;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Repositories\Contracts\Base\BaseRepositoryInterface;

class PaymentRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }

    public function getModuleName(): string
    {
        return 'payment';
    }

    protected function getModelClass(): string
    {
        return Payment::class;
    }
}
