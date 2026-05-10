<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesOrderRequest;
use App\Models\Branch;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['branch', 'cashier'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('payment_status', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($bq) use ($search) {
                            $bq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('cashier', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->payment_status, function ($query, $paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->orderBy('created_at', 'desc');

        $salesOrders = $query->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('sales-orders/index', [
            'data' => $salesOrders,
            'filters' => $request->only(['search', 'per_page', 'status', 'payment_status']),
            'statusOptions' => ['pending', 'confirmed', 'paid', 'shipped', 'completed', 'cancelled', 'refunded'],
            'paymentStatusOptions' => ['pending', 'partial', 'paid', 'refunded'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cashiers = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('sales-orders/create', [
            'branches' => $branches,
            'cashiers' => $cashiers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SalesOrderRequest $request)
    {
        $data = $request->validated();
        $data['order_number'] = $this->generateOrderNumber();

        SalesOrder::create($data);

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['branch', 'cashier', 'items.product']);

        return Inertia::render('sales-orders/show', [
            'salesOrder' => $salesOrder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        $salesOrder->load(['branch', 'cashier']);

        $branches = Branch::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cashiers = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('sales-orders/edit', [
            'salesOrder' => $salesOrder,
            'branches' => $branches,
            'cashiers' => $cashiers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalesOrderRequest $request, SalesOrder $salesOrder)
    {
        $salesOrder->update($request->validated());

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order deleted successfully.');
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'SO';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }
}
