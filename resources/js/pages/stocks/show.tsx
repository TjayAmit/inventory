import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Package, TrendingUp, TrendingDown, AlertTriangle, CheckCircle, XCircle, History, Plus, Minus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppContentWrapper from '@/components/app-content-wrapper';

interface StockData {
    id: number;
    productId: number;
    productName: string;
    productCode: string;
    productBarcode: string | null;
    categoryName: string | null;
    unit: string;
    quantity: number;
    reorderPoint: number;
    maxStock: number;
    isLowStock: boolean;
    isInStock: boolean;
    isOverStock: boolean;
    stockStatus: 'in_stock' | 'low_stock' | 'out_of_stock' | 'over_stock';
    lastRestockedAt: string | null;
}

interface ProductData {
    id: number;
    name: string;
    productCode: string;
    barcode: string | null;
    unit: string;
    categoryName: string | null;
    reorderPoint: number;
    maxStock: number;
    isActive: boolean;
}

interface Movement {
    id: number;
    type: string;
    typeLabel: string;
    quantityBefore: number;
    quantityChange: number;
    quantityAfter: number;
    isPositive: boolean;
    referenceNumber: string | null;
    notes: string | null;
    performedByName: string;
    createdAt: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface StocksShowProps {
    stock: StockData;
    product: ProductData;
    movements: {
        data: Movement[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    movementTypes: Record<string, string>;
    can: {
        adjust: boolean;
    };
}

const stockStatusConfig = {
    in_stock:     { label: 'In Stock',     icon: CheckCircle,  className: 'text-green-600',  bg: 'bg-green-50 border-green-200' },
    low_stock:    { label: 'Low Stock',    icon: AlertTriangle, className: 'text-yellow-600', bg: 'bg-yellow-50 border-yellow-200' },
    out_of_stock: { label: 'Out of Stock', icon: XCircle,      className: 'text-red-600',    bg: 'bg-red-50 border-red-200' },
    over_stock:   { label: 'Overstocked', icon: TrendingUp,   className: 'text-blue-600',   bg: 'bg-blue-50 border-blue-200' },
};

const ADDITION_TYPES = ['initial', 'purchase', 'return', 'adjustment'];
const DEDUCTION_TYPES = ['sale', 'damage', 'loss'];

export default function StocksShow({ stock, product, movements, movementTypes, can }: StocksShowProps) {
    const statusConfig = stockStatusConfig[stock.stockStatus];
    const StatusIcon = statusConfig.icon;

    const { data, setData, post, processing, errors, reset } = useForm({
        type: 'purchase',
        quantity: '',
        notes: '',
        reference_number: '',
    });

    const isDeductionType = DEDUCTION_TYPES.includes(data.type);
    const isAdjustmentType = data.type === 'adjustment';

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/stocks/${product.id}/adjust`, {
            onSuccess: () => reset(),
        });
    };

    const fillPercentage = stock.maxStock > 0
        ? Math.min(100, (stock.quantity / stock.maxStock) * 100)
        : 0;

    return (
        <>
            <Head title={`Stock — ${product.name}`} />
            <AppContentWrapper>
                <div className="space-y-6">
                    {/* Header */}
                    <div className="flex items-center gap-4">
                        <Link href="/stocks">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-1" />
                                Back
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{product.name}</h1>
                            <p className="text-sm text-muted-foreground">{product.productCode}{product.barcode ? ` · ${product.barcode}` : ''}</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Stock Level Card */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card className={`border-2 ${statusConfig.bg}`}>
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Package className="w-5 h-5" />
                                        Current Stock Level
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-end gap-3 mb-4">
                                        <span className="text-5xl font-bold text-foreground">{stock.quantity}</span>
                                        <span className="text-xl text-muted-foreground mb-1">{product.unit}</span>
                                        <span className={`ml-auto flex items-center gap-1 text-sm font-medium ${statusConfig.className}`}>
                                            <StatusIcon className="w-4 h-4" />
                                            {statusConfig.label}
                                        </span>
                                    </div>

                                    {/* Stock bar */}
                                    <div className="space-y-1 mb-4">
                                        <div className="h-3 rounded-full bg-muted overflow-hidden">
                                            <div
                                                className={`h-full rounded-full transition-all ${
                                                    stock.stockStatus === 'out_of_stock' ? 'bg-red-500' :
                                                    stock.stockStatus === 'low_stock' ? 'bg-yellow-500' :
                                                    stock.stockStatus === 'over_stock' ? 'bg-blue-500' : 'bg-green-500'
                                                }`}
                                                style={{ width: `${fillPercentage}%` }}
                                            />
                                        </div>
                                        <div className="flex justify-between text-xs text-muted-foreground">
                                            <span>0</span>
                                            <span>Reorder: {stock.reorderPoint}</span>
                                            <span>Max: {stock.maxStock}</span>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-3 gap-4 pt-3 border-t">
                                        <div className="text-center">
                                            <p className="text-lg font-semibold">{stock.reorderPoint}</p>
                                            <p className="text-xs text-muted-foreground">Reorder Point</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-lg font-semibold">{stock.maxStock}</p>
                                            <p className="text-xs text-muted-foreground">Max Stock</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-xs text-muted-foreground">Last Restocked</p>
                                            <p className="text-sm font-medium">
                                                {stock.lastRestockedAt
                                                    ? new Date(stock.lastRestockedAt).toLocaleDateString()
                                                    : 'Never'}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Movement History */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <History className="w-5 h-5" />
                                        Movement History
                                        <Badge variant="secondary" className="ml-auto">{movements.total} total</Badge>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {movements.data.length === 0 ? (
                                        <p className="text-sm text-muted-foreground text-center py-6">No movements recorded yet.</p>
                                    ) : (
                                        <div className="space-y-2">
                                            {movements.data.map((movement) => (
                                                <div
                                                    key={movement.id}
                                                    className="flex items-center gap-3 p-3 rounded-lg bg-muted/30 hover:bg-muted/50 transition-colors"
                                                >
                                                    <div className={`p-1.5 rounded-full ${movement.isPositive ? 'bg-green-100' : 'bg-red-100'}`}>
                                                        {movement.isPositive
                                                            ? <Plus className="w-3 h-3 text-green-600" />
                                                            : <Minus className="w-3 h-3 text-red-600" />
                                                        }
                                                    </div>
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-medium">{movement.typeLabel}</span>
                                                            {movement.referenceNumber && (
                                                                <span className="text-xs text-muted-foreground">#{movement.referenceNumber}</span>
                                                            )}
                                                        </div>
                                                        {movement.notes && (
                                                            <p className="text-xs text-muted-foreground truncate">{movement.notes}</p>
                                                        )}
                                                        <p className="text-xs text-muted-foreground">
                                                            {new Date(movement.createdAt).toLocaleString()} · {movement.performedByName}
                                                        </p>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className={`text-sm font-bold ${movement.isPositive ? 'text-green-600' : 'text-red-600'}`}>
                                                            {movement.isPositive ? '+' : ''}{movement.quantityChange}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {movement.quantityBefore} → {movement.quantityAfter}
                                                        </p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                    {movements.last_page > 1 && (
                                        <p className="text-xs text-muted-foreground text-center mt-3">
                                            Showing {movements.data.length} of {movements.total} movements
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Adjustment Form */}
                        {can.adjust && (
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="text-base">Adjust Stock</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div className="space-y-1.5">
                                                <Label htmlFor="type">Movement Type</Label>
                                                <Select
                                                    value={data.type}
                                                    onValueChange={(v) => setData('type', v)}
                                                >
                                                    <SelectTrigger id="type" className="w-full">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="initial">Initial Stock</SelectItem>
                                                        <SelectItem value="purchase">Purchase / Restock</SelectItem>
                                                        <SelectItem value="return">Return</SelectItem>
                                                        <SelectItem value="sale">Sale</SelectItem>
                                                        <SelectItem value="damage">Damage</SelectItem>
                                                        <SelectItem value="loss">Loss</SelectItem>
                                                        <SelectItem value="adjustment">Manual Adjustment</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                {errors.type && <p className="text-xs text-destructive">{errors.type}</p>}
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="quantity">
                                                    Quantity
                                                    {isDeductionType && (
                                                        <span className="text-xs text-muted-foreground ml-1">(will be deducted)</span>
                                                    )}
                                                    {isAdjustmentType && (
                                                        <span className="text-xs text-muted-foreground ml-1">(use negative to reduce)</span>
                                                    )}
                                                </Label>
                                                <div className="relative">
                                                    <Input
                                                        id="quantity"
                                                        type="number"
                                                        min={isAdjustmentType ? undefined : 1}
                                                        placeholder={isAdjustmentType ? 'e.g. 10 or -5' : 'e.g. 50'}
                                                        value={data.quantity}
                                                        onChange={(e) => setData('quantity', e.target.value)}
                                                        className={isDeductionType ? 'border-red-200 focus:border-red-400' : ''}
                                                    />
                                                    {isDeductionType && (
                                                        <TrendingDown className="absolute right-3 top-2.5 w-4 h-4 text-red-400" />
                                                    )}
                                                </div>
                                                {errors.quantity && <p className="text-xs text-destructive">{errors.quantity}</p>}
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="reference_number">Reference # <span className="text-muted-foreground text-xs">(optional)</span></Label>
                                                <Input
                                                    id="reference_number"
                                                    placeholder="e.g. PO-2024-001"
                                                    value={data.reference_number}
                                                    onChange={(e) => setData('reference_number', e.target.value)}
                                                />
                                                {errors.reference_number && <p className="text-xs text-destructive">{errors.reference_number}</p>}
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="notes">Notes <span className="text-muted-foreground text-xs">(optional)</span></Label>
                                                <Textarea
                                                    id="notes"
                                                    placeholder="Add a note about this adjustment..."
                                                    rows={3}
                                                    value={data.notes}
                                                    onChange={(e) => setData('notes', e.target.value)}
                                                />
                                                {errors.notes && <p className="text-xs text-destructive">{errors.notes}</p>}
                                            </div>

                                            <Button
                                                type="submit"
                                                className="w-full"
                                                disabled={processing || !data.quantity}
                                            >
                                                {processing ? 'Saving...' : 'Apply Adjustment'}
                                            </Button>
                                        </form>
                                    </CardContent>
                                </Card>

                                {/* Product Info Card */}
                                <Card className="mt-4">
                                    <CardHeader className="pb-2">
                                        <CardTitle className="text-sm text-muted-foreground">Product Info</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Category</span>
                                            <span>{product.categoryName ?? '—'}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Unit</span>
                                            <span>{product.unit}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Status</span>
                                            <Badge variant={product.isActive ? 'default' : 'secondary'}>
                                                {product.isActive ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                        <div className="pt-2 border-t">
                                            <Link href={`/products/${product.id}`}>
                                                <Button variant="outline" size="sm" className="w-full">
                                                    View Product Details
                                                </Button>
                                            </Link>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        )}
                    </div>
                </div>
            </AppContentWrapper>
        </>
    );
}

StocksShow.layout = {
    breadcrumbs: [
        { title: 'Stock Management', href: '/stocks' },
        { title: 'Product Stock', href: '#' },
    ],
};
