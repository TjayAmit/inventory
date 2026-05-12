<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalesOrderApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = SalesOrder::with(['branch', 'cashier'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('branch', fn ($bq) => $bq->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('cashier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('order_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('order_date', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return SalesOrderResource::collection($orders);
    }

    public function store(SalesOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['order_number'] = $this->generateOrderNumber();

        $order = SalesOrder::create($data);

        return response()->json([
            'message' => 'Sales order created successfully.',
            'data'    => new SalesOrderResource($order->load(['branch', 'cashier', 'items.product'])),
        ], 201);
    }

    public function show(SalesOrder $salesOrder): SalesOrderResource
    {
        return new SalesOrderResource($salesOrder->load(['branch', 'cashier', 'items.product']));
    }

    public function update(SalesOrderRequest $request, SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->update($request->validated());

        return response()->json([
            'message' => 'Sales order updated successfully.',
            'data'    => new SalesOrderResource($salesOrder->load(['branch', 'cashier', 'items.product'])),
        ]);
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->delete();

        return response()->json(['message' => 'Sales order deleted successfully.']);
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'SO';
        $date   = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }
}
