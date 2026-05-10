import { Head, Link } from '@inertiajs/react';
import { ShoppingCart, ArrowLeft, Package, Receipt, DollarSign, TrendingUp, Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as salesItemsIndex, edit as salesItemsEdit } from '@/routes/sales-items';
import type { SalesItemShowProps } from '@/types/sales-item';

export default function Show({ salesItem }: SalesItemShowProps) {
    const profit = parseFloat(salesItem.profit || '0');
    const profitMargin = parseFloat(salesItem.total_price) > 0 
        ? (profit / parseFloat(salesItem.total_price) * 100).toFixed(1)
        : '0.0';

    return (
        <>
            <Head title={`Sales Item - ${salesItem.product?.name || 'Details'}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={salesItemsIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <ShoppingCart className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">
                                    {salesItem.product?.name || 'Sales Item Details'}
                                </h1>
                                <p className="text-sm text-muted-foreground">
                                    Order: {salesItem.salesOrder?.order_number || '-'}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={salesItemsEdit(salesItem.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Item
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {profit > 0 ? (
                        <Badge variant="default" className="text-sm px-3 py-1">Profitable</Badge>
                    ) : profit < 0 ? (
                        <Badge variant="destructive" className="text-sm px-3 py-1">Loss</Badge>
                    ) : (
                        <Badge variant="outline" className="text-sm px-3 py-1">Break Even</Badge>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Receipt className="h-5 w-5" />
                                Order Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Order Number</p>
                                    <p className="text-lg font-mono">{salesItem.salesOrder?.order_number || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Item ID</p>
                                    <p className="text-lg">#{salesItem.id}</p>
                                </div>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Product</p>
                                <p className="text-lg">{salesItem.product?.name || '-'}</p>
                                <p className="text-sm text-muted-foreground">SKU: {salesItem.product?.sku || '-'}</p>
                            </div>
                            {salesItem.inventoryBatch && (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Inventory Batch</p>
                                        <p className="text-sm">{salesItem.inventoryBatch.batch_number}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Quantity & Pricing
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Quantity</p>
                                    <p className="text-2xl font-bold">{salesItem.quantity}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Unit Price</p>
                                    <p className="text-2xl font-bold">${parseFloat(salesItem.unit_price).toFixed(2)}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Discount</p>
                                    <p className="text-sm">${parseFloat(salesItem.discount_amount || '0').toFixed(2)}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Tax</p>
                                    <p className="text-sm">${parseFloat(salesItem.tax_amount || '0').toFixed(2)}</p>
                                </div>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Total Price</p>
                                <p className="text-xl font-bold">${parseFloat(salesItem.total_price).toFixed(2)}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Cost Analysis
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Unit Cost</p>
                                    <p className="text-lg">${salesItem.unit_cost ? parseFloat(salesItem.unit_cost).toFixed(2) : '-'}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Total Cost</p>
                                    <p className="text-lg">${salesItem.total_cost ? parseFloat(salesItem.total_cost).toFixed(2) : '-'}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5" />
                                Profit Analysis
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Profit</p>
                                    <p className={`text-lg font-bold ${profit >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                        ${profit.toFixed(2)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Profit Margin</p>
                                    <p className={`text-lg font-bold ${parseFloat(profitMargin) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                        {profitMargin}%
                                    </p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Created</p>
                                    <p className="text-sm">{new Date(salesItem.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Updated</p>
                                    <p className="text-sm">{new Date(salesItem.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
