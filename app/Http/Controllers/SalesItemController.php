<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesItemRequest;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SalesItem::with(['salesOrder', 'product'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('salesOrder', function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        $salesItems = $query->paginate($request->per_page ?? 10)
            ->withQueryString();

        $salesOrders = SalesOrder::select('id', 'order_number')
            ->orderBy('order_number')
            ->get();

        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('sales-items/index', [
            'data' => $salesItems,
            'filters' => $request->only(['search', 'per_page']),
            'salesOrders' => $salesOrders,
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salesOrders = SalesOrder::select('id', 'order_number')
            ->orderBy('order_number')
            ->get();

        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $inventoryBatches = InventoryBatch::select('id', 'batch_number')
            ->orderBy('batch_number')
            ->get();

        return Inertia::render('sales-items/create', [
            'salesOrders' => $salesOrders,
            'products' => $products,
            'inventoryBatches' => $inventoryBatches,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SalesItemRequest $request)
    {
        $data = $request->validated();
        
        // Calculate derived values
        $data['total_price'] = ($data['quantity'] * $data['unit_price']) - ($data['discount_amount'] ?? 0);
        $data['total_cost'] = $data['quantity'] * ($data['unit_cost'] ?? 0);
        $data['profit'] = $data['total_price'] - $data['total_cost'];
        
        SalesItem::create($data);

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesItem $salesItem)
    {
        $salesItem->load(['salesOrder', 'product', 'inventoryBatch']);

        return Inertia::render('sales-items/show', [
            'salesItem' => $salesItem,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesItem $salesItem)
    {
        $salesItem->load(['salesOrder', 'product']);

        $salesOrders = SalesOrder::select('id', 'order_number')
            ->orderBy('order_number')
            ->get();

        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $inventoryBatches = InventoryBatch::select('id', 'batch_number')
            ->orderBy('batch_number')
            ->get();

        return Inertia::render('sales-items/edit', [
            'salesItem' => $salesItem,
            'salesOrders' => $salesOrders,
            'products' => $products,
            'inventoryBatches' => $inventoryBatches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalesItemRequest $request, SalesItem $salesItem)
    {
        $data = $request->validated();
        
        // Recalculate derived values
        $data['total_price'] = ($data['quantity'] * $data['unit_price']) - ($data['discount_amount'] ?? 0);
        $data['total_cost'] = $data['quantity'] * ($data['unit_cost'] ?? 0);
        $data['profit'] = $data['total_price'] - $data['total_cost'];
        
        $salesItem->update($data);

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesItem $salesItem)
    {
        $salesItem->delete();

        return redirect()->route('sales-items.index')
            ->with('success', 'Sales item deleted successfully.');
    }
}
