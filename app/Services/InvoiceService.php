<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function list(Request $request): LengthAwarePaginator
    {
        return SalesOrder::with(['branch', 'cashier'])
            ->where('status', '!=', 'draft')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('cashier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('branch', fn ($bq) => $bq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_method, fn ($q, $pm) => $q->where('payment_method', $pm))
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();
    }

    public function createDraft(Request $request): SalesOrder
    {
        $user     = $request->user();
        $branchId = $user->branch_id ?? Branch::where('is_main_branch', true)->value('id');

        return SalesOrder::create([
            'order_number'   => $this->generateInvoiceNumber(),
            'branch_id'      => $branchId,
            'cashier_id'     => $user->id,
            'order_date'     => now()->toDateString(),
            'order_time'     => now()->toTimeString(),
            'status'         => 'draft',
            'payment_status' => 'pending',
        ]);
    }

    public function getWithProducts(SalesOrder $invoice): array
    {
        $invoice->load(['items.product', 'branch', 'cashier']);

        $props = ['invoice' => $invoice];

        if ($invoice->status === 'draft') {
            $branchId = $invoice->branch_id;
            $props['products'] = Product::where('is_active', true)
                ->with(['inventory' => fn ($q) => $q->where('branch_id', $branchId)])
                ->orderBy('name')
                ->get()
                ->map(fn ($p) => [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'selling_price' => $p->selling_price,
                    'cost_price'    => $p->cost_price,
                    'unit'          => $p->unit,
                    'is_taxable'    => $p->is_taxable,
                    'stock'         => $p->inventory->sum('quantity_on_hand'),
                ]);
        }

        return $props;
    }

    public function addItem(Request $request, SalesOrder $invoice): void
    {
        abort_unless($invoice->status === 'draft', 422, 'Cannot modify a finalized invoice.');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        DB::transaction(function () use ($invoice, $product, $validated) {
            $existing = $invoice->items()->where('product_id', $product->id)->first();

            if ($existing) {
                $this->applyItemTotals($existing, $existing->quantity + $validated['quantity'], $product);
                $existing->save();
            } else {
                $item = new SalesItem(['product_id' => $product->id, 'sales_order_id' => $invoice->id]);
                $this->applyItemTotals($item, $validated['quantity'], $product);
                $item->save();
            }

            $this->recalculateTotals($invoice);
        });
    }

    public function updateItem(Request $request, SalesOrder $invoice, SalesItem $salesItem): void
    {
        abort_unless($invoice->status === 'draft', 422, 'Cannot modify a finalized invoice.');

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($invoice, $salesItem, $validated) {
            $this->applyItemTotals($salesItem, $validated['quantity'], $salesItem->product);
            $salesItem->save();
            $this->recalculateTotals($invoice);
        });
    }

    public function removeItem(SalesOrder $invoice, SalesItem $salesItem): void
    {
        abort_unless($invoice->status === 'draft', 422, 'Cannot modify a finalized invoice.');

        DB::transaction(function () use ($invoice, $salesItem) {
            $salesItem->delete();
            $this->recalculateTotals($invoice);
        });
    }

    public function checkout(Request $request, SalesOrder $invoice): void
    {
        abort_unless($invoice->status === 'draft', 422, 'Invoice already finalized.');
        abort_unless($invoice->items()->exists(), 422, 'Cannot checkout an empty invoice.');

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,gcash,maya',
            'paid_amount'    => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        $total = (float) $invoice->total_amount;
        $paid  = (float) $validated['paid_amount'];

        $invoice->update([
            'status'         => 'paid',
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'paid_amount'    => $paid,
            'change_amount'  => max(0, $paid - $total),
            'notes'          => $validated['notes'] ?? null,
        ]);
    }

    public function cancelDraft(SalesOrder $invoice): void
    {
        abort_unless($invoice->status === 'draft', 403, 'Only draft invoices can be cancelled.');

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });
    }

    private function applyItemTotals(SalesItem $item, int $qty, Product $product): void
    {
        $unitPrice  = (float) $product->selling_price;
        $unitCost   = (float) $product->cost_price;
        $taxAmount  = $product->is_taxable ? round($unitPrice * $qty * 0.12, 2) : 0.0;
        $totalPrice = round($unitPrice * $qty, 2);
        $totalCost  = round($unitCost  * $qty, 4);

        $item->product_id      = $product->id;
        $item->quantity        = $qty;
        $item->unit_price      = $unitPrice;
        $item->unit_cost       = $unitCost;
        $item->discount_amount = 0;
        $item->tax_amount      = $taxAmount;
        $item->total_price     = $totalPrice;
        $item->total_cost      = $totalCost;
        $item->profit          = $totalPrice - $totalCost;
    }

    private function recalculateTotals(SalesOrder $invoice): void
    {
        $invoice->refresh();
        $subtotal = (float) $invoice->items()->sum('total_price');
        $tax      = (float) $invoice->items()->sum('tax_amount');
        $discount = (float) $invoice->discount_amount;

        $invoice->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $tax,
            'total_amount' => $subtotal + $tax - $discount,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $seq  = str_pad(
            SalesOrder::where('order_number', 'like', "INV-{$date}-%")->count() + 1,
            4, '0', STR_PAD_LEFT
        );
        return "INV-{$date}-{$seq}";
    }
}
