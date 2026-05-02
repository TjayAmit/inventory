import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Edit, Barcode, Package, Tag, DollarSign, Truck, Building, Weight, Beaker, FileText } from 'lucide-react';
import AppContentWrapper from '@/components/app-content-wrapper';

interface Product {
    id: number;
    name: string;
    productCode: string;
    barcode: string | null;
    description: string | null;
    price: number;
    costPrice: number | null;
    categoryId: number | null;
    categoryName: string | null;
    isActive: boolean;
    isTaxable: boolean;
    unit: string;
    weight: number | null;
    volume: number | null;
    brand: string | null;
    manufacturer: string | null;
    supplier: string | null;
    reorderPoint: number;
    maxStock: number;
    notes: string | null;
    formattedPrice: string;
    formattedCostPrice: string | null;
    formattedProfitMargin: string | null;
    currentStock: number | null;
    createdAt: string;
    updatedAt: string;
}

interface ShowProps {
    product: Product;
    can: {
        edit: boolean;
        delete: boolean;
        manage: boolean;
    };
}

export default function ProductsShow({ product, can }: ShowProps) {
    return (
        <>
            <Head title={product.name} />
            <AppContentWrapper>
                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/products">Products</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{product.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                {/* Page Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">{product.name}</h1>
                        <div className="flex items-center gap-2 mt-1">
                            <p className="text-muted-foreground">{product.productCode}</p>
                            {product.barcode && (
                                <span className="font-mono text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded">
                                    {product.barcode}
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {can.edit && (
                            <Button asChild variant="outline">
                                <Link href={`/products/${product.id}/edit`}>
                                    <Edit className="w-4 h-4 mr-2" />
                                    Edit
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="w-5 h-5" />
                                    Basic Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Category</p>
                                        <p className="font-medium">{product.categoryName || 'Uncategorized'}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Brand</p>
                                        <p className="font-medium">{product.brand || '-'}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Unit</p>
                                        <p className="font-medium uppercase">{product.unit}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Status</p>
                                        <Badge variant={product.isActive ? 'default' : 'secondary'}>
                                            {product.isActive ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>
                                </div>
                                {product.description && (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Description</p>
                                        <p className="mt-1">{product.description}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Pricing */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <DollarSign className="w-5 h-5" />
                                    Pricing Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Selling Price</p>
                                        <p className="text-2xl font-bold">{product.formattedPrice}</p>
                                    </div>
                                    {product.formattedCostPrice && (
                                        <div>
                                            <p className="text-sm text-muted-foreground">Cost Price</p>
                                            <p className="text-lg font-medium">{product.formattedCostPrice}</p>
                                        </div>
                                    )}
                                    {product.formattedProfitMargin && (
                                        <div>
                                            <p className="text-sm text-muted-foreground">Profit Margin</p>
                                            <p className="text-lg font-medium text-green-600">{product.formattedProfitMargin}</p>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4">
                                    <p className="text-sm text-muted-foreground">Taxable</p>
                                    <p className="font-medium">{product.isTaxable ? 'Yes' : 'No'}</p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Stock */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Tag className="w-5 h-5" />
                                    Stock Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Current Stock</p>
                                        <p className="text-2xl font-bold">{product.currentStock ?? '-'}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Reorder Point</p>
                                        <p className="text-lg font-medium">{product.reorderPoint}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Max Stock</p>
                                        <p className="text-lg font-medium">{product.maxStock}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Additional Info */}
                        {(product.weight || product.volume || product.manufacturer || product.supplier || product.notes) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="w-5 h-5" />
                                        Additional Information
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 gap-4">
                                        {product.weight && (
                                            <div className="flex items-center gap-2">
                                                <Weight className="w-4 h-4 text-muted-foreground" />
                                                <div>
                                                    <p className="text-sm text-muted-foreground">Weight</p>
                                                    <p className="font-medium">{product.weight} kg</p>
                                                </div>
                                            </div>
                                        )}
                                        {product.volume && (
                                            <div className="flex items-center gap-2">
                                                <Beaker className="w-4 h-4 text-muted-foreground" />
                                                <div>
                                                    <p className="text-sm text-muted-foreground">Volume</p>
                                                    <p className="font-medium">{product.volume} L</p>
                                                </div>
                                            </div>
                                        )}
                                        {product.manufacturer && (
                                            <div className="flex items-center gap-2">
                                                <Building className="w-4 h-4 text-muted-foreground" />
                                                <div>
                                                    <p className="text-sm text-muted-foreground">Manufacturer</p>
                                                    <p className="font-medium">{product.manufacturer}</p>
                                                </div>
                                            </div>
                                        )}
                                        {product.supplier && (
                                            <div className="flex items-center gap-2">
                                                <Truck className="w-4 h-4 text-muted-foreground" />
                                                <div>
                                                    <p className="text-sm text-muted-foreground">Supplier</p>
                                                    <p className="font-medium">{product.supplier}</p>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                    {product.notes && (
                                        <div className="mt-4 pt-4 border-t">
                                            <p className="text-sm text-muted-foreground">Notes</p>
                                            <p className="mt-1">{product.notes}</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Barcode Card */}
                        {product.barcode ? (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Barcode className="w-5 h-5" />
                                        Barcode
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="bg-muted p-4 rounded-lg text-center">
                                        <p className="font-mono text-lg">{product.barcode}</p>
                                        <p className="text-xs text-muted-foreground mt-1">EAN-13</p>
                                    </div>
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Barcode className="w-5 h-5" />
                                        Barcode
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground mb-3">No barcode assigned</p>
                                    {can.manage && (
                                        <Button variant="outline" size="sm" className="w-full" asChild>
                                            <Link href={`/products/${product.id}/edit`}>Generate Barcode</Link>
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Metadata */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Metadata</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Created</span>
                                    <span>{new Date(product.createdAt).toLocaleDateString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Updated</span>
                                    <span>{new Date(product.updatedAt).toLocaleDateString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Product ID</span>
                                    <span className="font-mono">#{product.id}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </AppContentWrapper>
        </>
    );
}

ProductsShow.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: '/products',
        },
    ],
};
