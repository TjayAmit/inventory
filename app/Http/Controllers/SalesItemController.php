<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesItemRequest;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesOrder;
use App\Services\SalesItemService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesItemController extends Controller
{
    public function __construct(private readonly SalesItemService $salesItemService) {}

    public function index(Request $request)
    {
        return Inertia::render('sales-items/index', [
            'data'        => $this->salesItemService->list($request)->withQueryString(),
            'filters'     => $request->only(['search', 'per_page']),
            'salesOrders' => SalesOrder::select('id', 'order_number')->orderBy('order_number')->get(),
            'products'    => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('sales-items/create', [
            'salesOrders'      => SalesOrder::select('id', 'order_number')->orderBy('order_number')->get(),
            'products'         => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
            'inventoryBatches' => InventoryBatch::select('id', 'batch_number')->orderBy('batch_number')->get(),
        ]);
    }

    public function store(SalesItemRequest $request)
    {
        $this->salesItemService->create($request);

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item created successfully.');
    }

    public function show(SalesItem $salesItem)
    {
        $salesItem->load(['salesOrder', 'product', 'inventoryBatch']);

        return Inertia::render('sales-items/show', [
            'salesItem' => $salesItem,
        ]);
    }

    public function edit(SalesItem $salesItem)
    {
        $salesItem->load(['salesOrder', 'product']);

        return Inertia::render('sales-items/edit', [
            'salesItem'        => $salesItem,
            'salesOrders'      => SalesOrder::select('id', 'order_number')->orderBy('order_number')->get(),
            'products'         => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
            'inventoryBatches' => InventoryBatch::select('id', 'batch_number')->orderBy('batch_number')->get(),
        ]);
    }

    public function update(SalesItemRequest $request, SalesItem $salesItem)
    {
        $this->salesItemService->update($request, $salesItem);

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item updated successfully.');
    }

    public function destroy(SalesItem $salesItem)
    {
        $this->salesItemService->delete($salesItem);

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item deleted successfully.');
    }
}
