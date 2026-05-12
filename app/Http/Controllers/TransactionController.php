<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = SalesOrder::with(['branch', 'cashier'])
            ->whereIn('status', ['paid', 'completed', 'cancelled', 'refunded'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('cashier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('branch', fn ($bq) => $bq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_method, fn ($q, $pm) => $q->where('payment_method', $pm))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        return Inertia::render('transactions/index', [
            'data'           => $transactions,
            'filters'        => $request->only(['search', 'per_page', 'status', 'payment_method', 'date_from', 'date_to']),
            'statusOptions'  => ['paid', 'completed', 'cancelled', 'refunded'],
            'paymentMethods' => ['cash', 'gcash', 'maya'],
        ]);
    }

    public function show(SalesOrder $transaction)
    {
        abort_if($transaction->status === 'draft', 404);

        $transaction->load(['branch', 'cashier', 'items.product']);

        return Inertia::render('transactions/show', [
            'transaction' => $transaction,
        ]);
    }
}
