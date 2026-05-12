<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    private const LOG = 'suppliers';
    private const FIELDS = ['name', 'contact_person', 'email', 'phone', 'is_active'];

    public function __construct(private readonly SupplierService $supplierService) {}

    public function index(Request $request)
    {
        return Inertia::render('suppliers/index', [
            'data'    => $this->supplierService->list($request)->withQueryString(),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('suppliers/create');
    }

    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplierService->create($request);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($supplier)
            ->withProperties(['attributes' => $supplier->only(self::FIELDS)])
            ->event('created')
            ->log('created');

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchaseOrders');

        return Inertia::render('suppliers/show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('suppliers/edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $before = $supplier->only(self::FIELDS);

        $updated = $this->supplierService->update($request, $supplier);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->withProperties(['old' => $before, 'attributes' => $updated->only(self::FIELDS)])
            ->event('updated')
            ->log('updated');

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $snapshot = $supplier->only(self::FIELDS);

        $this->supplierService->delete($supplier);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($supplier)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('deleted');

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
