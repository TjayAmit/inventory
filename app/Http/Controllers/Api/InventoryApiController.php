<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $inventory = Inventory::with(['product', 'branch'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('product', fn ($pq) =>
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%")
                )->orWhereHas('branch', fn ($bq) =>
                    $bq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%")
                );
            })
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->when($request->boolean('out_of_stock'), fn ($q) => $q->outOfStock())
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return InventoryResource::collection($inventory);
    }

    public function store(InventoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['quantity_available'] = $data['quantity_on_hand'] - ($data['quantity_reserved'] ?? 0);

        $inventory = Inventory::create($data);

        return response()->json([
            'message' => 'Inventory record created successfully.',
            'data'    => new InventoryResource($inventory->load(['product', 'branch'])),
        ], 201);
    }

    public function show(Inventory $inventory): InventoryResource
    {
        return new InventoryResource($inventory->load(['product', 'branch']));
    }

    public function update(InventoryRequest $request, Inventory $inventory): JsonResponse
    {
        $data = $request->validated();
        $data['quantity_available'] = $data['quantity_on_hand'] - ($data['quantity_reserved'] ?? 0);

        $inventory->update($data);

        return response()->json([
            'message' => 'Inventory record updated successfully.',
            'data'    => new InventoryResource($inventory->load(['product', 'branch'])),
        ]);
    }

    public function destroy(Inventory $inventory): JsonResponse
    {
        $inventory->delete();

        return response()->json(['message' => 'Inventory record deleted successfully.']);
    }

    public function products(): JsonResponse
    {
        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $products]);
    }

    public function branches(): JsonResponse
    {
        $branches = Branch::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $branches]);
    }
}
