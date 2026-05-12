<?php

namespace App\Http\Controllers;

use App\Models\SalesItem;
use App\Models\SalesOrder;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    private const LOG = 'sales-orders';

    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        return Inertia::render('invoice/index', [
            'data'           => $this->invoiceService->list($request),
            'filters'        => $request->only(['search', 'per_page', 'status', 'payment_method']),
            'statusOptions'  => ['paid', 'completed', 'cancelled', 'refunded'],
            'paymentMethods' => ['cash', 'gcash', 'maya'],
        ]);
    }

    public function store(Request $request)
    {
        $invoice = $this->invoiceService->createDraft($request);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['attributes' => ['order_number' => $invoice->order_number, 'status' => $invoice->status]])
            ->event('created')
            ->log('Invoice draft created');

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(SalesOrder $invoice)
    {
        return Inertia::render('invoice/show', $this->invoiceService->getWithProducts($invoice));
    }

    public function addItem(Request $request, SalesOrder $invoice)
    {
        $this->invoiceService->addItem($request, $invoice);

        return back();
    }

    public function updateItem(Request $request, SalesOrder $invoice, SalesItem $salesItem)
    {
        $this->invoiceService->updateItem($request, $invoice, $salesItem);

        return back();
    }

    public function removeItem(SalesOrder $invoice, SalesItem $salesItem)
    {
        $this->invoiceService->removeItem($invoice, $salesItem);

        return back();
    }

    public function checkout(Request $request, SalesOrder $invoice)
    {
        $before = $invoice->only(['status', 'payment_status', 'total_amount']);

        $this->invoiceService->checkout($request, $invoice);

        $invoice->refresh();

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties([
                'old'        => $before,
                'attributes' => $invoice->only(['status', 'payment_status', 'payment_method', 'total_amount', 'paid_amount', 'change_amount']),
            ])
            ->event('updated')
            ->log('Invoice checked out');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->order_number . ' completed.');
    }

    public function destroy(SalesOrder $invoice)
    {
        $snapshot = $invoice->only(['order_number', 'status', 'total_amount']);

        $this->invoiceService->cancelDraft($invoice);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('Invoice draft cancelled');

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice cancelled.');
    }
}
