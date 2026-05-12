import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Receipt } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index as txIndex } from '@/routes/transactions';
import AppLayout from '@/layouts/app-layout';
import type { TransactionShowProps, PaymentMethod } from '@/types';

const PM_LABELS: Record<PaymentMethod, string> = { cash: 'Cash', gcash: 'GCash', maya: 'Maya' };

const STATUS_COLORS: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    paid:      'default',
    completed: 'default',
    cancelled: 'destructive',
    refunded:  'destructive',
};

const fmt = (v: string | number) => `₱${parseFloat(String(v)).toFixed(2)}`;

export default function Show({ transaction }: TransactionShowProps) {
    const total    = parseFloat(transaction.total_amount)  || 0;
    const subtotal = parseFloat(transaction.subtotal)      || 0;
    const tax      = parseFloat(transaction.tax_amount)    || 0;
    const paid     = parseFloat(transaction.paid_amount)   || 0;
    const change   = parseFloat(transaction.change_amount) || 0;

    return (
        <>
            <Head title={transaction.order_number} />
            <div className="w-full flex flex-col gap-4 p-4 lg:p-6 max-w-3xl">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="outline" size="icon" onClick={() => router.get(txIndex())}>
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-bold font-mono">{transaction.order_number}</h1>
                            <p className="text-sm text-muted-foreground">
                                {new Date(transaction.order_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })}
                                {transaction.order_time ? ` · ${transaction.order_time.slice(0, 5)}` : ''}
                            </p>
                        </div>
                    </div>
                    <Badge variant={STATUS_COLORS[transaction.status] ?? 'secondary'} className="capitalize text-sm px-3 py-1">
                        {transaction.status}
                    </Badge>
                </div>

                {/* Info Grid */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span className="text-muted-foreground text-xs uppercase tracking-wide">Branch</span>
                        <p className="font-medium mt-0.5">{transaction.branch?.name ?? '—'}</p>
                    </div>
                    <div>
                        <span className="text-muted-foreground text-xs uppercase tracking-wide">Cashier</span>
                        <p className="font-medium mt-0.5">{transaction.cashier?.name ?? '—'}</p>
                    </div>
                    <div>
                        <span className="text-muted-foreground text-xs uppercase tracking-wide">Payment</span>
                        <p className="font-medium mt-0.5">
                            {transaction.payment_method ? PM_LABELS[transaction.payment_method] : '—'}
                        </p>
                    </div>
                    {transaction.notes && (
                        <div>
                            <span className="text-muted-foreground text-xs uppercase tracking-wide">Notes</span>
                            <p className="font-medium mt-0.5">{transaction.notes}</p>
                        </div>
                    )}
                </div>

                <Separator />

                {/* Items */}
                <Card>
                    <CardHeader className="pb-2 pt-4 px-4">
                        <CardTitle className="text-sm flex items-center gap-2">
                            <Receipt className="h-4 w-4" />
                            Items ({transaction.items.length})
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
                                {transaction.items.map((item) => (
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
                            <div className="flex justify-between text-muted-foreground">
                                <span>Subtotal</span><span className="tabular-nums">{fmt(subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                                <span>Tax (12%)</span><span className="tabular-nums">{fmt(tax)}</span>
                            </div>
                            <Separator />
                            <div className="flex justify-between font-bold text-base">
                                <span>Total</span><span className="tabular-nums">{fmt(total)}</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                                <span>Paid</span>
                                <span className="text-green-600 font-medium tabular-nums">{fmt(paid)}</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                                <span>Change</span><span className="tabular-nums">{fmt(change)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Show.layout = (page: React.ReactElement<TransactionShowProps>) => (
    <AppLayout breadcrumbs={[
        { title: 'Transactions', href: txIndex().url },
        { title: page.props?.transaction?.order_number ?? 'View', href: '' },
    ]}>
        {page}
    </AppLayout>
);
