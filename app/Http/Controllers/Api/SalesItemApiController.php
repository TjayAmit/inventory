<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesItemRequest;
use App\Http\Resources\SalesItemResource;
use App\Models\SalesItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalesItemApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = SalesItem::with(['salesOrder', 'product'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('product', fn ($pq) =>
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%")
                )->orWhereHas('salesOrder', fn ($sq) =>
                    $sq->where('order_number', 'like', "%{$search}%")
                );
            })
            ->when($request->filled('sales_order_id'), fn ($q) => $q->where('sales_order_id', $request->sales_order_id))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->product_id))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return SalesItemResource::collection($items);
    }

    public function store(SalesItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['total_price'] = ($data['quantity'] * $data['unit_price']) - ($data['discount_amount'] ?? 0);
        $data['total_cost']  = $data['quantity'] * ($data['unit_cost'] ?? 0);
        $data['profit']      = $data['total_price'] - $data['total_cost'];

        $item = SalesItem::create($data);

        return response()->json([
            'message' => 'Sales item created successfully.',
            'data'    => new SalesItemResource($item->load(['salesOrder', 'product'])),
        ], 201);
    }

    public function show(SalesItem $salesItem): SalesItemResource
    {
        return new SalesItemResource($salesItem->load(['salesOrder', 'product', 'inventoryBatch']));
    }

    public function update(SalesItemRequest $request, SalesItem $salesItem): JsonResponse
    {
        $data = $request->validated();
        $data['total_price'] = ($data['quantity'] * $data['unit_price']) - ($data['discount_amount'] ?? 0);
        $data['total_cost']  = $data['quantity'] * ($data['unit_cost'] ?? 0);
        $data['profit']      = $data['total_price'] - $data['total_cost'];

        $salesItem->update($data);

        return response()->json([
            'message' => 'Sales item updated successfully.',
            'data'    => new SalesItemResource($salesItem->load(['salesOrder', 'product'])),
        ]);
    }

    public function destroy(SalesItem $salesItem): JsonResponse
    {
        $salesItem->delete();

        return response()->json(['message' => 'Sales item deleted successfully.']);
    }
}
