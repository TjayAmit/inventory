<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::with('category')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => new ProductResource($product->load('category')),
        ], 201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data'    => new ProductResource($product->load('category')),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function categories(): JsonResponse
    {
        $categories = ProductCategory::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $categories]);
    }
}
