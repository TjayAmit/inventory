<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $suppliers = Supplier::when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('supplier_code', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return SupplierResource::collection($suppliers);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'message' => 'Supplier created successfully.',
            'data'    => new SupplierResource($supplier),
        ], 201);
    }

    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'data'    => new SupplierResource($supplier),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully.']);
    }
}
