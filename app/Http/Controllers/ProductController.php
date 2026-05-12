<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    private const LOG = 'products';
    private const FIELDS = ['name', 'sku', 'selling_price', 'cost_price', 'is_active', 'reorder_level'];

    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request)
    {
        return Inertia::render('products/index', [
            'data'    => $this->productService->list($request)->withQueryString(),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('products/create', [
            'categories' => ProductCategory::select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->create($request);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties(['attributes' => $product->only(self::FIELDS)])
            ->event('created')
            ->log('created');

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category');

        return Inertia::render('products/show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        $product->load('category');

        return Inertia::render('products/edit', [
            'product'    => $product,
            'categories' => ProductCategory::select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $before = $product->only(self::FIELDS);

        $updated = $this->productService->update($request, $product);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->withProperties(['old' => $before, 'attributes' => $updated->only(self::FIELDS)])
            ->event('updated')
            ->log('updated');

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $snapshot = $product->only(self::FIELDS);

        $this->productService->delete($product);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('deleted');

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
