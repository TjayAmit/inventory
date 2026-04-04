<?php

namespace App\Http\Controllers\API;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\ProductFiltersDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Product\StoreProductAPIRequest;
use App\Http\Requests\API\Product\UpdateProductAPIRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductAPIController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:product.view')->only(['index', 'show']);
        $this->middleware('permission:product.create')->only(['store']);
        $this->middleware('permission:product.edit')->only(['update']);
        $this->middleware('permission:product.delete')->only(['destroy']);
        $this->middleware('permission:product.manage')->only(['toggleStatus', 'generateBarcode']);
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = ProductFiltersDTO::fromRequest($request->all());
            $perPage = $request->get('per_page', 15);
            
            $products = $this->productService->getPaginatedProducts($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
                'filters' => $filters->toArray(),
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductAPIRequest $request): JsonResponse
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

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product created successfully.'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $productDto = $this->productService->getProductById($product->id);

            if (!$productDto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $productDto,
                'message' => 'Product retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductAPIRequest $request, Product $product): JsonResponse
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

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Product updated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->deleteProduct($product->id);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get all active products.
     */
    public function active(): JsonResponse
    {
        try {
            $products = $this->productService->getActiveProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Active products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term' => 'required|string|min:1',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $limit = $validated['limit'] ?? 10;
            $products = $this->productService->searchProducts($validated['term'], $limit);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products searched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Find product by barcode.
     */
    public function findByBarcode(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'barcode' => 'required|string|size:13'
            ]);

            $product = $this->productService->getProductByBarcode($validated['barcode']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product found successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Find product by product code.
     */
    public function findByProductCode(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_code' => 'required|string|max:50'
            ]);

            $product = $this->productService->getProductByProductCode($validated['product_code']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product found successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->toggleProductStatus($product->id);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Product status updated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Generate barcode for product.
     */
    public function generateBarcode(Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->generateBarcodeForProduct($product->id);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Barcode generated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate barcode: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                'data' => $statistics,
                'message' => 'Statistics retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk update products.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'updates' => 'required|array',
                'updates.*.id' => 'required|integer|exists:products,id',
                'updates.*.is_active' => 'boolean',
                'updates.*.category_id' => 'nullable|integer|exists:categories,id'
            ]);

            $updated = $this->productService->bulkUpdateProducts($validated['updates']);

            return response()->json([
                'success' => true,
                'data' => ['updated_count' => $updated],
                'message' => "Updated {$updated} products successfully."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get products by price range.
     */
    public function byPriceRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'min_price' => 'required|numeric|min:0',
                'max_price' => 'required|numeric|min:0'
            ]);

            $products = $this->productService->getProductsByPriceRange(
                (float) $validated['min_price'],
                (float) $validated['max_price']
            );

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Import products from array.
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'products' => 'required|array',
                'products.*.name' => 'required|string|max:200',
                'products.*.price' => 'required|numeric|min:0.01',
                'products.*.product_code' => 'nullable|string|max:50',
                'products.*.barcode' => 'nullable|string|size:13',
                'products.*.category_id' => 'nullable|integer|exists:categories,id'
            ]);

            $results = $this->productService->importProducts($validated['products']);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Import completed successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
