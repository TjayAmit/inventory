<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        return Inertia::render('inventory/index', [
            'data'     => $this->inventoryService->list($request)->withQueryString(),
            'filters'  => $request->only(['search', 'per_page']),
            'products' => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::select('id', 'name', 'code')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('inventory/create', [
            'products' => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::select('id', 'name', 'code')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(InventoryRequest $request)
    {
        $this->inventoryService->create($request);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record created successfully.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'branch']);

        return Inertia::render('inventory/show', [
            'inventory' => $inventory,
        ]);
    }

    public function edit(Inventory $inventory)
    {
        $inventory->load(['product', 'branch']);

        return Inertia::render('inventory/edit', [
            'inventory' => $inventory,
            'products'  => Product::select('id', 'name', 'sku')->where('is_active', true)->orderBy('name')->get(),
            'branches'  => Branch::select('id', 'name', 'code')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(InventoryRequest $request, Inventory $inventory)
    {
        $this->inventoryService->update($request, $inventory);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $this->inventoryService->delete($inventory);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record deleted successfully.');
    }
}
