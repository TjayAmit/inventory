<?php

namespace App\DTOs\Payment;

use Illuminate\Http\Request;

class PaymentData
{
    public function __construct(
        public readonly string $payment_number,
        public readonly int $sales_order_id,
        public readonly ?int $customer_id = null,
        public readonly int $received_by,
        public readonly string $payment_method = 'cash',
        public readonly string $status = 'pending',
        public readonly float $amount = 0.00,
        public readonly float $refunded_amount = 0.00,
        public readonly string $currency = 'USD',
        public readonly float $exchange_rate = 1.000000,
        public readonly ?string $card_type = null,
        public readonly ?string $card_last_four = null,
        public readonly ?string $transaction_id = null,
        public readonly ?string $authorization_code = null,
        public readonly ?string $check_number = null,
        public readonly ?string $bank_name = null,
        public readonly ?string $account_number = null,
        public readonly ?string $notes = null,
        public readonly string $payment_date,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            payment_number: $request->validated('payment_number'),
            sales_order_id: $request->validated('sales_order_id'),
            customer_id: $request->validated('customer_id'),
            received_by: $request->validated('received_by'),
            payment_method: $request->validated('payment_method', 'cash'),
            status: $request->validated('status', 'pending'),
            amount: $request->validated('amount', 0.00),
            refunded_amount: $request->validated('refunded_amount', 0.00),
            currency: $request->validated('currency', 'USD'),
            exchange_rate: $request->validated('exchange_rate', 1.000000),
            card_type: $request->validated('card_type'),
            card_last_four: $request->validated('card_last_four'),
            transaction_id: $request->validated('transaction_id'),
            authorization_code: $request->validated('authorization_code'),
            check_number: $request->validated('check_number'),
            bank_name: $request->validated('bank_name'),
            account_number: $request->validated('account_number'),
            notes: $request->validated('notes'),
            payment_date: $request->validated('payment_date'),
        );
    }

    public static function fromModel(Payment $payment): self
    {
        return new self(
            payment_number: $payment->payment_number,
            sales_order_id: $payment->sales_order_id,
            customer_id: $payment->customer_id,
            received_by: $payment->received_by,
            payment_method: $payment->payment_method,
            status: $payment->status,
            amount: $payment->amount,
            refunded_amount: $payment->refunded_amount,
            currency: $payment->currency,
            exchange_rate: $payment->exchange_rate,
            card_type: $payment->card_type,
            card_last_four: $payment->card_last_four,
            transaction_id: $payment->transaction_id,
            authorization_code: $payment->authorization_code,
            check_number: $payment->check_number,
            bank_name: $payment->bank_name,
            account_number: $payment->account_number,
            notes: $payment->notes,
            payment_date: $payment->payment_date->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
