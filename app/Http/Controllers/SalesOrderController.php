<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesOrderRequest;
use App\Models\Branch;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\SalesOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function __construct(private readonly SalesOrderService $salesOrderService) {}

    public function index(Request $request)
    {
        return Inertia::render('sales-orders/index', [
            'data'                => $this->salesOrderService->list($request)->withQueryString(),
            'filters'             => $request->only(['search', 'per_page', 'status', 'payment_status']),
            'statusOptions'       => ['pending', 'confirmed', 'paid', 'shipped', 'completed', 'cancelled', 'refunded'],
            'paymentStatusOptions' => ['pending', 'partial', 'paid', 'refunded'],
        ]);
    }

    public function create()
    {
        return Inertia::render('sales-orders/create', [
            'branches' => Branch::select('id', 'name')->where('is_active', true)->orderBy('name')->get(),
            'cashiers' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(SalesOrderRequest $request)
    {
        $this->salesOrderService->create($request);

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order created successfully.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['branch', 'cashier', 'items.product']);

        return Inertia::render('sales-orders/show', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function edit(SalesOrder $salesOrder)
    {
        $salesOrder->load(['branch', 'cashier']);

        return Inertia::render('sales-orders/edit', [
            'salesOrder' => $salesOrder,
            'branches'   => Branch::select('id', 'name')->where('is_active', true)->orderBy('name')->get(),
            'cashiers'   => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(SalesOrderRequest $request, SalesOrder $salesOrder)
    {
        $this->salesOrderService->update($request, $salesOrder);

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $this->salesOrderService->delete($salesOrder);

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order deleted successfully.');
    }
}
