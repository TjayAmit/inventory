<?php

namespace App\Http\Controllers;

use App\DTOs\Stock\AdjustStockDTO;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view products')->only(['index', 'show', 'movements']);
        $this->middleware('permission:manage products')->only(['adjust']);
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search'       => $request->get('search'),
            'category_id'  => $request->get('category_id'),
            'stock_status' => $request->get('stock_status'),
        ];

        $perPage   = $request->get('per_page', 15);
        $stocks    = $this->stockService->getPaginatedStocks($filters, $perPage);
        $stats     = $this->stockService->getStatistics();

        $categoryRepo   = app(\App\Repositories\Eloquent\CategoryRepository::class);
        $categoryOptions = $categoryRepo->getForDropdown();

        return Inertia::render('stocks/index', [
            'stocks'     => $stocks,
            'filters'    => $filters,
            'stats'      => $stats,
            'categories' => $categoryOptions,
            'can'        => [
                'adjust' => auth()->user()->can('create', Product::class),
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        $stockDto  = $this->stockService->getOrInitializeStock($product->id);
        $movements = $this->stockService->getProductMovements($product->id, 10);

        return Inertia::render('stocks/show', [
            'stock'        => $stockDto->toArray(),
            'product'      => [
                'id'          => $product->id,
                'name'        => $product->name,
                'productCode' => $product->product_code,
                'barcode'     => $product->barcode,
                'unit'        => $product->unit,
                'categoryId'  => $product->category_id,
                'categoryName'=> $product->category?->name,
                'reorderPoint'=> $product->reorder_point,
                'maxStock'    => $product->max_stock,
                'isActive'    => $product->is_active,
            ],
            'movements'    => $movements,
            'movementTypes'=> \App\Models\StockMovement::TYPE_LABELS,
            'can'          => [
                'adjust' => auth()->user()->can('create', Product::class),
            ],
        ]);
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'type'             => ['required', 'in:' . implode(',', \App\Models\StockMovement::TYPES)],
            'quantity'         => ['required', 'integer', 'min:1'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $dto = new AdjustStockDTO(
            productId:       $product->id,
            type:            $request->input('type'),
            quantityChange:  (int) $request->input('quantity'),
            notes:           $request->input('notes'),
            referenceNumber: $request->input('reference_number'),
            performedBy:     auth()->id(),
        );

        $this->stockService->adjustStock($dto);

        return redirect()
            ->route('stocks.show', $product)
            ->with('success', 'Stock adjusted successfully.');
    }
}
