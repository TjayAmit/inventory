<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'branch'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('branch', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        $inventory = $query->paginate($request->per_page ?? 10)
            ->withQueryString();

        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('inventory/index', [
            'data' => $inventory,
            'filters' => $request->only(['search', 'per_page']),
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('inventory/create', [
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InventoryRequest $request)
    {
        $data = $request->validated();
        $data['quantity_available'] = $data['quantity_on_hand'] - ($data['quantity_reserved'] ?? 0);

        Inventory::create($data);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'branch']);

        return Inertia::render('inventory/show', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        $inventory->load(['product', 'branch']);

        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('inventory/edit', [
            'inventory' => $inventory,
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InventoryRequest $request, Inventory $inventory)
    {
        $data = $request->validated();
        $data['quantity_available'] = $data['quantity_on_hand'] - ($data['quantity_reserved'] ?? 0);

        $inventory->update($data);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory record deleted successfully.');
    }
}
