import { Head, router, useForm } from '@inertiajs/react';
import { useState, FormEvent } from 'react';
import { ArrowLeft, Minus, Plus, Trash2, ShoppingCart, CheckCircle, Receipt, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    index as invoicesIndex,
    addItem, updateItem, removeItem, checkout, destroy,
} from '@/routes/invoices';
import AppLayout from '@/layouts/app-layout';
import type { InvoiceShowProps, InvoiceItem, InvoiceProduct, PaymentMethod } from '@/types';

const PM_LABELS: Record<PaymentMethod, string> = { cash: 'Cash', gcash: 'GCash', maya: 'Maya' };
const fmt = (v: string | number) => `₱${parseFloat(String(v)).toFixed(2)}`;

// ─── POS MODE ────────────────────────────────────────────────────────────────

function POSView({ invoice, products }: Required<InvoiceShowProps>) {
    const [search, setSearch]           = useState('');
    const [cancelling, setCancelling]   = useState(false);
    const [addingId, setAddingId]       = useState<number | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        payment_method: 'cash' as PaymentMethod,
        paid_amount: '',
        notes: '',
    });

    const filtered = products.filter(
        (p) => p.name.toLowerCase().includes(search.toLowerCase()) || p.sku.toLowerCase().includes(search.toLowerCase())
    );

    const total    = parseFloat(invoice.total_amount) || 0;
    const taxAmt   = parseFloat(invoice.tax_amount)   || 0;
    const subtotal = parseFloat(invoice.subtotal)      || 0;
    const paid     = parseFloat(data.paid_amount)      || 0;
    const change   = Math.max(0, paid - total);

    const handleAddProduct = (product: InvoiceProduct) => {
        setAddingId(product.id);
        router.post(addItem(invoice.id), { product_id: product.id, quantity: 1 }, {
            preserveScroll: true,
            onFinish: () => setAddingId(null),
        });
    };

    const handleQtyChange = (item: InvoiceItem, delta: number) => {
        const newQty = item.quantity + delta;
        if (newQty < 1) return;
        router.put(updateItem({ salesOrder: invoice.id, salesItem: item.id }), { quantity: newQty }, { preserveScroll: true });
    };

    const handleRemoveItem = (item: InvoiceItem) => {
        router.delete(removeItem({ salesOrder: invoice.id, salesItem: item.id }), { preserveScroll: true });
    };

    const handleCancelInvoice = () => {
        if (!confirm('Cancel and discard this invoice?')) return;
        setCancelling(true);
        router.delete(destroy(invoice.id), { onFinish: () => setCancelling(false) });
    };

    const handleCheckout = (e: FormEvent) => {
        e.preventDefault();
        post(checkout.url(invoice.id));
    };

    return (
        <div className="w-full flex flex-col gap-4 p-4 lg:p-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" onClick={() => router.get(invoicesIndex())}>
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-xl font-bold">{invoice.order_number}</h1>
                        <p className="text-sm text-muted-foreground">Draft Invoice — {invoice.branch?.name}</p>
                    </div>
                </div>
                <Button variant="destructive" size="sm" onClick={handleCancelInvoice} disabled={cancelling}>
                    <X className="h-4 w-4 mr-1" />
                    {cancelling ? 'Cancelling…' : 'Cancel Invoice'}
                </Button>
            </div>

            {/* POS Split Layout */}
            <div className="flex flex-col lg:flex-row gap-4 items-start">
                {/* Left: Product Grid */}
                <div className="flex flex-col flex-1 w-full min-w-0">
                    <div className="mb-3">
                        <Input
                            placeholder="Search products by name or SKU…"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="h-9"
                        />
                    </div>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 max-h-[calc(100vh-220px)] overflow-y-auto pb-2">
                        {filtered.length === 0 ? (
                            <p className="col-span-full text-sm text-muted-foreground text-center py-8">No products found.</p>
                        ) : filtered.map((p) => (
                            <button
                                key={p.id}
                                onClick={() => handleAddProduct(p)}
                                disabled={addingId === p.id}
                                className="flex flex-col text-left p-3 rounded-lg border bg-card hover:bg-accent hover:border-primary transition-colors disabled:opacity-60 cursor-pointer"
                            >
                                <span className="text-xs text-muted-foreground font-mono mb-1 truncate w-full">{p.sku}</span>
                                <span className="text-sm font-medium leading-tight line-clamp-2">{p.name}</span>
                                <div className="mt-2 flex items-center justify-between gap-1">
                                    <span className="text-sm font-bold text-primary">{fmt(p.selling_price)}</span>
                                    <span className={`text-xs shrink-0 ${p.stock > 0 ? 'text-muted-foreground' : 'text-destructive'}`}>
                                        {p.stock > 0 ? `${p.stock}` : '0'}
                                    </span>
                                </div>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Right: Cart + Checkout */}
                <div className="w-full lg:w-96 shrink-0 flex flex-col gap-3">
                    {/* Cart Items */}
                    <Card>
                        <CardHeader className="pb-2 pt-4 px-4">
                            <CardTitle className="text-sm flex items-center gap-2">
                                <ShoppingCart className="h-4 w-4" />
                                Cart ({invoice.items.length} item{invoice.items.length !== 1 ? 's' : ''})
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-4 pb-4">
                            {invoice.items.length === 0 ? (
                                <p className="text-sm text-muted-foreground text-center py-6">Cart is empty. Click a product to add.</p>
                            ) : (
                                <div className="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    {invoice.items.map((item) => (
                                        <div key={item.id} className="flex items-center gap-2">
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium truncate">{item.product?.name}</p>
                                                <p className="text-xs text-muted-foreground">{fmt(item.unit_price)} × {item.quantity}</p>
                                            </div>
                                            <div className="flex items-center gap-1 shrink-0">
                                                <Button variant="outline" size="icon" className="h-6 w-6" onClick={() => handleQtyChange(item, -1)}>
                                                    <Minus className="h-3 w-3" />
                                                </Button>
                                                <span className="w-6 text-center text-sm tabular-nums">{item.quantity}</span>
                                                <Button variant="outline" size="icon" className="h-6 w-6" onClick={() => handleQtyChange(item, 1)}>
                                                    <Plus className="h-3 w-3" />
                                                </Button>
                                                <Button variant="ghost" size="icon" className="h-6 w-6 text-destructive hover:text-destructive" onClick={() => handleRemoveItem(item)}>
                                                    <Trash2 className="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <span className="text-sm font-medium w-20 text-right shrink-0">{fmt(item.total_price)}</span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {invoice.items.length > 0 && (
                                <>
                                    <Separator className="my-3" />
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between text-muted-foreground">
                                            <span>Subtotal</span><span>{fmt(subtotal)}</span>
                                        </div>
                                        <div className="flex justify-between text-muted-foreground">
                                            <span>Tax (12%)</span><span>{fmt(taxAmt)}</span>
                                        </div>
                                        <Separator />
                                        <div className="flex justify-between font-bold text-base">
                                            <span>Total</span><span>{fmt(total)}</span>
                                        </div>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    {/* Checkout Form */}
                    {invoice.items.length > 0 && (
                        <Card>
                            <CardHeader className="pb-2 pt-4 px-4">
                                <CardTitle className="text-sm">Payment</CardTitle>
                            </CardHeader>
                            <CardContent className="px-4 pb-4">
                                <form onSubmit={handleCheckout} className="space-y-3">
                                    <div>
                                        <Label className="text-xs mb-1.5 block">Payment Method</Label>
                                        <div className="flex gap-2">
                                            {(['cash', 'gcash', 'maya'] as PaymentMethod[]).map((pm) => (
                                                <button
                                                    key={pm}
                                                    type="button"
                                                    onClick={() => setData('payment_method', pm)}
                                                    className={`flex-1 py-2 rounded-md text-xs font-medium border transition-colors ${
                                                        data.payment_method === pm
                                                            ? 'bg-primary text-primary-foreground border-primary'
                                                            : 'bg-background text-foreground border-border hover:bg-accent'
                                                    }`}
                                                >
                                                    {PM_LABELS[pm]}
                                                </button>
                                            ))}
                                        </div>
                                        {errors.payment_method && <p className="text-xs text-destructive mt-1">{errors.payment_method}</p>}
                                    </div>

                                    <div>
                                        <Label className="text-xs mb-1.5 block">Amount Tendered (₱)</Label>
                                        <Input
                                            type="number"
                                            min={total}
                                            step="0.01"
                                            placeholder={total.toFixed(2)}
                                            value={data.paid_amount}
                                            onChange={(e) => setData('paid_amount', e.target.value)}
                                            className="h-9 text-right font-mono tabular-nums"
                                        />
                                        {errors.paid_amount && <p className="text-xs text-destructive mt-1">{errors.paid_amount}</p>}
                                    </div>

                                    {paid > 0 && (
                                        <div className={`flex justify-between items-center rounded-md px-3 py-2 ${paid >= total ? 'bg-green-50 dark:bg-green-950' : 'bg-destructive/10'}`}>
                                            <span className="text-xs font-medium">Change</span>
                                            <span className={`text-sm font-bold ${paid >= total ? 'text-green-600' : 'text-destructive'}`}>
                                                {paid >= total ? fmt(change) : `Short ₱${(total - paid).toFixed(2)}`}
                                            </span>
                                        </div>
                                    )}

                                    <div>
                                        <Label className="text-xs mb-1.5 block">Notes (optional)</Label>
                                        <Input
                                            placeholder="Remarks…"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            className="h-9"
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing || !data.paid_amount || paid < total}
                                    >
                                        <CheckCircle className="h-4 w-4 mr-2" />
                                        {processing ? 'Processing…' : `Checkout — ${fmt(total)}`}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </div>
    );
}

// ─── RECEIPT MODE ────────────────────────────────────────────────────────────

const STATUS_COLORS: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    paid:      'default',
    completed: 'default',
    cancelled: 'destructive',
    refunded:  'destructive',
};

function ReceiptView({ invoice }: Pick<InvoiceShowProps, 'invoice'>) {
    const total    = parseFloat(invoice.total_amount)  || 0;
    const subtotal = parseFloat(invoice.subtotal)      || 0;
    const tax      = parseFloat(invoice.tax_amount)    || 0;
    const paid     = parseFloat(invoice.paid_amount)   || 0;
    const change   = parseFloat(invoice.change_amount) || 0;

    return (
        <div className="w-full flex flex-col gap-4 p-4 lg:p-6 max-w-3xl">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" onClick={() => router.get(invoicesIndex())}>
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-xl font-bold font-mono">{invoice.order_number}</h1>
                        <p className="text-sm text-muted-foreground">
                            {new Date(invoice.order_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })}
                            {invoice.order_time ? ` · ${invoice.order_time.slice(0, 5)}` : ''}
                        </p>
                    </div>
                </div>
                <Badge variant={STATUS_COLORS[invoice.status] ?? 'secondary'} className="capitalize text-sm px-3 py-1">
                    {invoice.status}
                </Badge>
            </div>

            {/* Info Grid */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span className="text-muted-foreground text-xs uppercase tracking-wide">Branch</span>
                    <p className="font-medium mt-0.5">{invoice.branch?.name ?? '—'}</p>
                </div>
                <div>
                    <span className="text-muted-foreground text-xs uppercase tracking-wide">Cashier</span>
                    <p className="font-medium mt-0.5">{invoice.cashier?.name ?? '—'}</p>
                </div>
                <div>
                    <span className="text-muted-foreground text-xs uppercase tracking-wide">Payment</span>
                    <p className="font-medium mt-0.5">{invoice.payment_method ? PM_LABELS[invoice.payment_method] : '—'}</p>
                </div>
                {invoice.notes && (
                    <div>
                        <span className="text-muted-foreground text-xs uppercase tracking-wide">Notes</span>
                        <p className="font-medium mt-0.5">{invoice.notes}</p>
                    </div>
                )}
            </div>

            <Separator />

            {/* Items */}
            <Card>
                <CardHeader className="pb-2 pt-4 px-4">
                    <CardTitle className="text-sm flex items-center gap-2">
                        <Receipt className="h-4 w-4" />
                        Items ({invoice.items.length})
                    </CardTitle>
                </CardHeader>
                <CardContent className="px-4 pb-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Product</TableHead>
                                <TableHead className="text-right">Qty</TableHead>
                                <TableHead className="text-right">Unit Price</TableHead>
                                <TableHead className="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {invoice.items.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell>
                                        <p className="font-medium">{item.product?.name}</p>
                                        <p className="text-xs text-muted-foreground font-mono">{item.product?.sku}</p>
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">{item.quantity}</TableCell>
                                    <TableCell className="text-right tabular-nums">{fmt(item.unit_price)}</TableCell>
                                    <TableCell className="text-right font-medium tabular-nums">{fmt(item.total_price)}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>

                    <Separator className="my-3" />

                    <div className="space-y-1.5 text-sm max-w-xs ml-auto">
                        <div className="flex justify-between text-muted-foreground"><span>Subtotal</span><span className="tabular-nums">{fmt(subtotal)}</span></div>
                        <div className="flex justify-between text-muted-foreground"><span>Tax (12%)</span><span className="tabular-nums">{fmt(tax)}</span></div>
                        <Separator />
                        <div className="flex justify-between font-bold text-base"><span>Total</span><span className="tabular-nums">{fmt(total)}</span></div>
                        <div className="flex justify-between text-muted-foreground"><span>Paid</span><span className="text-green-600 font-medium tabular-nums">{fmt(paid)}</span></div>
                        <div className="flex justify-between text-muted-foreground"><span>Change</span><span className="tabular-nums">{fmt(change)}</span></div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// ─── MAIN ────────────────────────────────────────────────────────────────────

export default function Show({ invoice, products }: InvoiceShowProps) {
    if (invoice.status === 'draft' && products) {
        return <POSView invoice={invoice} products={products} />;
    }
    return <ReceiptView invoice={invoice} />;
}

Show.layout = (page: React.ReactElement<InvoiceShowProps>) => (
    <AppLayout breadcrumbs={[
        { title: 'Invoice', href: invoicesIndex().url },
        { title: page.props?.invoice?.order_number ?? 'View', href: '' },
    ]}>
        {page}
    </AppLayout>
);
