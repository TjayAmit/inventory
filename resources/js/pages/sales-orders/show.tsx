import { Head, Link } from '@inertiajs/react';
import { ShoppingCart, ArrowLeft, Edit, Store, User, Calendar, DollarSign, FileText, Package, CreditCard, CheckCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index as salesOrdersIndex, edit as salesOrdersEdit } from '@/routes/sales-orders';
import type { SalesOrderShowProps } from '@/types/sales-order';

const statusColors: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    confirmed: 'secondary',
    paid: 'default',
    shipped: 'secondary',
    completed: 'default',
    cancelled: 'destructive',
    refunded: 'destructive',
};

const paymentStatusColors: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    partial: 'secondary',
    paid: 'default',
    refunded: 'destructive',
};

export default function Show({ salesOrder }: SalesOrderShowProps) {
    const formatCurrency = (value: string | number) => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        return `$${num.toFixed(2)}`;
    };

    return (
        <>
            <Head title={salesOrder.order_number} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={salesOrdersIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <ShoppingCart className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">{salesOrder.order_number}</h1>
                                <p className="text-sm text-muted-foreground">
                                    Created on {new Date(salesOrder.created_at).toLocaleDateString()}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={salesOrdersEdit(salesOrder.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Order
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Badge variant={statusColors[salesOrder.status] || 'secondary'} className="text-sm px-3 py-1">
                        {salesOrder.status_label || salesOrder.status}
                    </Badge>
                    <Badge variant={paymentStatusColors[salesOrder.payment_status] || 'secondary'} className="text-sm px-3 py-1">
                        {salesOrder.payment_status_label || salesOrder.payment_status}
                    </Badge>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Store className="h-5 w-5" />
                                Branch Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Branch</p>
                                <p className="text-lg">{salesOrder.branch?.name || 'Unknown'}</p>
                            </div>
                            <Separator />
                            <div className="flex items-center gap-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Cashier</p>
                                    <p className="text-sm">{salesOrder.cashier?.name || 'Unknown'}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Order Date
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Date</p>
                                <p className="text-lg">{new Date(salesOrder.order_date).toLocaleDateString()}</p>
                            </div>
                            {salesOrder.order_time && (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Time</p>
                                        <p className="text-sm">{salesOrder.order_time}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Payment Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Subtotal</span>
                                <span className="font-medium">{formatCurrency(salesOrder.subtotal)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Tax Amount</span>
                                <span className="font-medium">{formatCurrency(salesOrder.tax_amount)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Discount</span>
                                <span className="font-medium text-green-600">-{formatCurrency(salesOrder.discount_amount)}</span>
                            </div>
                            <Separator />
                            <div className="flex justify-between">
                                <span className="font-medium">Total Amount</span>
                                <span className="text-lg font-bold">{formatCurrency(salesOrder.total_amount)}</span>
                            </div>
                            <Separator />
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Paid Amount</span>
                                <span className="font-medium text-green-600">{formatCurrency(salesOrder.paid_amount)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Change Amount</span>
                                <span className="font-medium">{formatCurrency(salesOrder.change_amount)}</span>
                            </div>
                            {parseFloat(salesOrder.total_amount) > parseFloat(salesOrder.paid_amount) && (
                                <div className="flex justify-between text-destructive">
                                    <span>Remaining</span>
                                    <span className="font-medium">
                                        {formatCurrency(parseFloat(salesOrder.total_amount) - parseFloat(salesOrder.paid_amount))}
                                    </span>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CreditCard className="h-5 w-5" />
                                Payment Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center gap-3">
                                {salesOrder.payment_status === 'paid' ? (
                                    <CheckCircle className="h-8 w-8 text-green-500" />
                                ) : salesOrder.payment_status === 'partial' ? (
                                    <DollarSign className="h-8 w-8 text-yellow-500" />
                                ) : (
                                    <CreditCard className="h-8 w-8 text-gray-400" />
                                )}
                                <div>
                                    <p className="font-medium">
                                        {salesOrder.payment_status_label || salesOrder.payment_status}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {salesOrder.payment_status === 'paid'
                                            ? 'Payment completed'
                                            : salesOrder.payment_status === 'partial'
                                            ? 'Partial payment received'
                                            : 'Awaiting payment'}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {salesOrder.notes && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Notes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm whitespace-pre-wrap">{salesOrder.notes}</p>
                        </CardContent>
                    </Card>
                )}

                {salesOrder.items && salesOrder.items.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Order Items
                            </CardTitle>
                            <CardDescription>{salesOrder.items.length} item(s) in this order</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead className="text-right">Quantity</TableHead>
                                        <TableHead className="text-right">Unit Price</TableHead>
                                        <TableHead className="text-right">Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {salesOrder.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="font-medium">{item.product?.name || 'Unknown Product'}</span>
                                                    <span className="text-xs text-muted-foreground">{item.product?.sku || ''}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right">{item.quantity}</TableCell>
                                            <TableCell className="text-right">{formatCurrency(item.unit_price)}</TableCell>
                                            <TableCell className="text-right font-medium">{formatCurrency(item.total_price)}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>System Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span className="text-muted-foreground">Created:</span>{' '}
                                {new Date(salesOrder.created_at).toLocaleString()}
                            </div>
                            <div>
                                <span className="text-muted-foreground">Last Updated:</span>{' '}
                                {new Date(salesOrder.updated_at).toLocaleString()}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
