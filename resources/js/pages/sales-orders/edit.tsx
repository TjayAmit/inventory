import { Head, Link, useForm } from '@inertiajs/react';
import { ShoppingCart, ArrowLeft, Calculator } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { index as salesOrdersIndex, update as salesOrdersUpdate } from '@/routes/sales-orders';
import type { SalesOrderEditProps } from '@/types/sales-order';
import { useEffect, useState } from 'react';

export default function Edit({ salesOrder, branches, cashiers }: SalesOrderEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        branch_id: String(salesOrder.branch_id),
        cashier_id: String(salesOrder.cashier_id),
        order_date: salesOrder.order_date,
        order_time: salesOrder.order_time || '',
        status: salesOrder.status,
        subtotal: salesOrder.subtotal,
        tax_amount: salesOrder.tax_amount,
        discount_amount: salesOrder.discount_amount,
        total_amount: salesOrder.total_amount,
        paid_amount: salesOrder.paid_amount,
        change_amount: salesOrder.change_amount,
        payment_status: salesOrder.payment_status,
        notes: salesOrder.notes || '',
    });

    const [calculatedTotal, setCalculatedTotal] = useState(0);
    const [calculatedChange, setCalculatedChange] = useState(0);

    useEffect(() => {
        const subtotal = parseFloat(data.subtotal) || 0;
        const tax = parseFloat(data.tax_amount) || 0;
        const discount = parseFloat(data.discount_amount) || 0;
        const total = subtotal + tax - discount;
        setCalculatedTotal(total);
        setData('total_amount', total.toFixed(2));
    }, [data.subtotal, data.tax_amount, data.discount_amount]);

    useEffect(() => {
        const total = parseFloat(data.total_amount) || 0;
        const paid = parseFloat(data.paid_amount) || 0;
        const change = Math.max(0, paid - total);
        setCalculatedChange(change);
        setData('change_amount', change.toFixed(2));

        // Auto-update payment status based on paid amount
        if (paid === 0) {
            setData('payment_status', 'pending');
        } else if (paid >= total) {
            setData('payment_status', 'paid');
        } else {
            setData('payment_status', 'partial');
        }
    }, [data.paid_amount, data.total_amount]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(salesOrdersUpdate.url(salesOrder.id));
    };

    return (
        <>
            <Head title={`Edit ${salesOrder.order_number}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-5xl mx-auto">
                <div className="flex items-center gap-4">
                    <Link href={salesOrdersIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <ShoppingCart className="h-6 w-6 text-primary" />
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">Edit Sales Order</h1>
                            <p className="text-sm text-muted-foreground">{salesOrder.order_number}</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Order Information</CardTitle>
                            <CardDescription>Update the basic order details.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="branch">Branch *</Label>
                                    <Select
                                        value={data.branch_id}
                                        onValueChange={(value) => setData('branch_id', value)}
                                    >
                                        <SelectTrigger id="branch">
                                            <SelectValue placeholder="Select a branch" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {branches.map((branch) => (
                                                <SelectItem key={branch.id} value={String(branch.id)}>
                                                    {branch.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.branch_id && <p className="text-sm text-destructive">{errors.branch_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="cashier">Cashier *</Label>
                                    <Select
                                        value={data.cashier_id}
                                        onValueChange={(value) => setData('cashier_id', value)}
                                    >
                                        <SelectTrigger id="cashier">
                                            <SelectValue placeholder="Select a cashier" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {cashiers.map((cashier) => (
                                                <SelectItem key={cashier.id} value={String(cashier.id)}>
                                                    {cashier.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.cashier_id && <p className="text-sm text-destructive">{errors.cashier_id}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="order_date">Order Date *</Label>
                                    <Input
                                        id="order_date"
                                        type="date"
                                        value={data.order_date}
                                        onChange={(e) => setData('order_date', e.target.value)}
                                    />
                                    {errors.order_date && <p className="text-sm text-destructive">{errors.order_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="order_time">Order Time</Label>
                                    <Input
                                        id="order_time"
                                        type="time"
                                        value={data.order_time}
                                        onChange={(e) => setData('order_time', e.target.value)}
                                    />
                                    {errors.order_time && <p className="text-sm text-destructive">{errors.order_time}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Order Status *</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(value) => setData('status', value)}
                                >
                                    <SelectTrigger id="status">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="confirmed">Confirmed</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="shipped">Shipped</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                        <SelectItem value="refunded">Refunded</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calculator className="h-5 w-5" />
                                Payment Details
                            </CardTitle>
                            <CardDescription>Update the payment and pricing information.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="subtotal">Subtotal *</Label>
                                    <Input
                                        id="subtotal"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.subtotal}
                                        onChange={(e) => setData('subtotal', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.subtotal && <p className="text-sm text-destructive">{errors.subtotal}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tax_amount">Tax Amount *</Label>
                                    <Input
                                        id="tax_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.tax_amount}
                                        onChange={(e) => setData('tax_amount', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.tax_amount && <p className="text-sm text-destructive">{errors.tax_amount}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="discount_amount">Discount Amount *</Label>
                                    <Input
                                        id="discount_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.discount_amount}
                                        onChange={(e) => setData('discount_amount', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.discount_amount && <p className="text-sm text-destructive">{errors.discount_amount}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="total_amount">Total Amount</Label>
                                    <Input
                                        id="total_amount"
                                        type="number"
                                        step="0.01"
                                        value={data.total_amount}
                                        readOnly
                                        className="bg-muted"
                                    />
                                    <p className="text-xs text-muted-foreground">Auto-calculated</p>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="paid_amount">Paid Amount *</Label>
                                    <Input
                                        id="paid_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.paid_amount}
                                        onChange={(e) => setData('paid_amount', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.paid_amount && <p className="text-sm text-destructive">{errors.paid_amount}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="change_amount">Change Amount</Label>
                                    <Input
                                        id="change_amount"
                                        type="number"
                                        step="0.01"
                                        value={data.change_amount}
                                        readOnly
                                        className="bg-muted"
                                    />
                                    <p className="text-xs text-muted-foreground">Auto-calculated</p>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="payment_status">Payment Status *</Label>
                                <Select
                                    value={data.payment_status}
                                    onValueChange={(value) => setData('payment_status', value)}
                                >
                                    <SelectTrigger id="payment_status">
                                        <SelectValue placeholder="Select payment status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="partial">Partial</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="refunded">Refunded</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.payment_status && <p className="text-sm text-destructive">{errors.payment_status}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                            <CardDescription>Update any notes or comments about this order.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Enter any additional notes..."
                                    rows={4}
                                />
                                {errors.notes && <p className="text-sm text-destructive">{errors.notes}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Link href={salesOrdersIndex()}>
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}
