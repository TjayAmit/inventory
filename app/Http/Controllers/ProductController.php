<?php

namespace App\Http\Controllers;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\ProductFiltersDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->middleware('auth');
        $this->middleware('permission:product.view')->only(['index', 'show']);
        $this->middleware('permission:product.create')->only(['create', 'store']);
        $this->middleware('permission:product.edit')->only(['edit', 'update']);
        $this->middleware('permission:product.delete')->only(['destroy']);
        $this->middleware('permission:product.manage')->only(['toggleStatus', 'generateBarcode']);
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request): Response
    {
        $filters = ProductFiltersDTO::fromRequest($request->all());
        $perPage = $request->get('per_page', 15);
        
        $products = $this->productService->getPaginatedProducts($filters, $perPage);

        // Get categories for filter dropdown
        $categoryRepo = app(\App\Repositories\Eloquent\CategoryRepository::class);
        $categoryOptions = $categoryRepo->getForDropdown();

        return Inertia::render('products/index', [
            'products' => $products,
            'filters' => $filters->toArray(),
            'categories' => $categoryOptions,
            'can' => [
                'create' => auth()->user()->can('create', Product::class),
                'edit' => auth()->user()->can('create', Product::class), // Use create as proxy for edit permission
                'delete' => auth()->user()->can('create', Product::class), // Use create as proxy for delete permission
                'manage' => auth()->user()->can('create', Product::class), // Use create as proxy for manage permission
            ]
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): Response
    {
        // Get categories for dropdown
        $categories = \App\Repositories\Contracts\CategoryRepositoryInterface::class;
        $categoryRepo = app(\App\Repositories\Eloquent\CategoryRepository::class);
        $categoryOptions = $categoryRepo->getForDropdown();

        return Inertia::render('products/create', [
            'categories' => $categoryOptions,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $dto = new CreateProductDTO(
                name: $request->validated('name'),
                productCode: $request->validated('product_code'),
                barcode: $request->validated('barcode'),
                description: $request->validated('description'),
                price: (float) $request->validated('price'),
                costPrice: $request->validated('cost_price') ? (float) $request->validated('cost_price') : null,
                categoryId: $request->validated('category_id'),
                isActive: $request->validated('is_active', true),
                isTaxable: $request->validated('is_taxable', true),
                unit: $request->validated('unit', 'pcs'),
                weight: $request->validated('weight') ? (float) $request->validated('weight') : null,
                volume: $request->validated('volume') ? (float) $request->validated('volume') : null,
                brand: $request->validated('brand'),
                manufacturer: $request->validated('manufacturer'),
                supplier: $request->validated('supplier'),
                reorderPoint: $request->validated('reorder_point', 10),
                maxStock: $request->validated('max_stock', 1000),
                notes: $request->validated('notes')
            );

            $product = $this->productService->createProduct($dto);

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): Response
    {
        $productDto = $this->productService->getProductById($product->id);

        return Inertia::render('products/show', [
            'product' => $productDto,
            'can' => [
                'edit' => auth()->user()->can('update', $product),
                'delete' => auth()->user()->can('delete', $product),
                'manage' => auth()->user()->can('managePricing', $product),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): Response
    {
        $productDto = $this->productService->getProductById($product->id);
        
        // Get categories for dropdown
        $categoryRepo = app(\App\Repositories\Eloquent\CategoryRepository::class);
        $categoryOptions = $categoryRepo->getForDropdown();

        return Inertia::render('products/edit', [
            'product' => $productDto,
            'categories' => $categoryOptions,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $dto = new UpdateProductDTO(
                name: $request->validated('name'),
                productCode: $request->validated('product_code'),
                barcode: $request->validated('barcode'),
                description: $request->validated('description'),
                price: (float) $request->validated('price'),
                costPrice: $request->validated('cost_price') ? (float) $request->validated('cost_price') : null,
                categoryId: $request->validated('category_id'),
                isActive: $request->validated('is_active'),
                isTaxable: $request->validated('is_taxable'),
                unit: $request->validated('unit'),
                weight: $request->validated('weight') ? (float) $request->validated('weight') : null,
                volume: $request->validated('volume') ? (float) $request->validated('volume') : null,
                brand: $request->validated('brand'),
                manufacturer: $request->validated('manufacturer'),
                supplier: $request->validated('supplier'),
                reorderPoint: $request->validated('reorder_point'),
                maxStock: $request->validated('max_stock'),
                notes: $request->validated('notes'),
                productId: $product->id
            );

            $updatedProduct = $this->productService->updateProduct($product->id, $dto);

            return redirect()
                ->route('products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->productService->deleteProduct($product->id);

            return redirect()
                ->route('products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            $this->productService->toggleProductStatus($product->id);

            return redirect()
                ->back()
                ->with('success', 'Product status updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update product status: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate barcode for product.
     */
    public function generateBarcode(Product $product): RedirectResponse
    {
        try {
            $this->productService->generateBarcodeForProduct($product->id);

            return redirect()
                ->back()
                ->with('success', 'Barcode generated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to generate barcode: ' . $e->getMessage()]);
        }
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->get('term', '');
            $limit = $request->get('limit', 10);

            $products = $this->productService->searchProducts($term, $limit);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find product by barcode.
     */
    public function findByBarcode(Request $request): JsonResponse
    {
        try {
            $barcode = $request->validate([
                'barcode' => 'required|string|size:13'
            ])['barcode'];

            $product = $this->productService->getProductByBarcode($barcode);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find product by product code.
     */
    public function findByProductCode(Request $request): JsonResponse
    {
        try {
            $productCode = $request->validate([
                'product_code' => 'required|string|max:50'
            ])['product_code'];

            $product = $this->productService->getProductByProductCode($productCode);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for dropdown.
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->get('category_id');
            $products = $this->productService->getProductsForDropdown($categoryId);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by category.
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($categoryId);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by brand.
     */
    public function byBrand(string $brand): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByBrand($brand);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by supplier.
     */
    public function bySupplier(string $supplier): JsonResponse
    {
        try {
            $products = $this->productService->getProductsBySupplier($supplier);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products with barcodes.
     */
    public function withBarcodes(): JsonResponse
    {
        try {
            $products = $this->productService->getProductsWithBarcodes();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products without barcodes.
     */
    public function withoutBarcodes(): JsonResponse
    {
        try {
            $products = $this->productService->getProductsWithoutBarcodes();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products with profit margins.
     */
    public function withProfitMargins(): JsonResponse
    {
        try {
            $products = $this->productService->getProductsWithProfitMargins();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->productService->getProductStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update products.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $updates = $request->validate([
                'updates' => 'required|array',
                'updates.*.id' => 'required|integer|exists:products,id',
                'updates.*.is_active' => 'boolean',
                'updates.*.category_id' => 'nullable|integer|exists:categories,id'
            ])['updates'];

            $updated = $this->productService->bulkUpdateProducts($updates);

            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} products successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import products from CSV/Excel.
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
            ]);

            // This is a placeholder for CSV import functionality
            // In a real implementation, you would parse the file and call importProducts
            
            return response()->json([
                'success' => true,
                'message' => 'Import functionality will be implemented in Phase 3.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV.
     */
    public function export(Request $request): JsonResponse
    {
        try {
            // This is a placeholder for export functionality
            // In a real implementation, you would generate and return a CSV file
            
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented in Phase 3.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
