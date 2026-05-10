import { Head, Link } from '@inertiajs/react';
import { Package, ArrowLeft, Tag, DollarSign, Box, Layers, Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as productsIndex, edit as productsEdit } from '@/routes/products';
import type { ProductShowProps } from '@/types/product';

export default function Show({ product }: ProductShowProps) {
    return (
        <>
            <Head title={product.name} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={productsIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <Package className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">{product.name}</h1>
                                <p className="text-sm text-muted-foreground">SKU: {product.sku}</p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={productsEdit(product.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Product
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {product.is_active ? (
                        <Badge variant="default" className="text-sm px-3 py-1">Active</Badge>
                    ) : (
                        <Badge variant="destructive" className="text-sm px-3 py-1">Inactive</Badge>
                    )}
                    {product.is_trackable && (
                        <Badge variant="outline" className="text-sm px-3 py-1">Trackable</Badge>
                    )}
                    {product.is_taxable && (
                        <Badge variant="secondary" className="text-sm px-3 py-1">Taxable</Badge>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Tag className="h-5 w-5" />
                                Product Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">SKU</p>
                                    <p className="text-sm">{product.sku}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Barcode</p>
                                    <p className="text-sm">{product.barcode || 'Not set'}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Category</p>
                                    <p className="text-sm">{product.category?.name || 'Uncategorized'}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Brand</p>
                                    <p className="text-sm">{product.brand || 'Not set'}</p>
                                </div>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Unit</p>
                                <p className="text-sm">{product.unit}</p>
                            </div>
                            {product.description && (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Description</p>
                                        <p className="text-sm">{product.description}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Pricing
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Cost Price</p>
                                    <p className="text-lg">${parseFloat(product.cost_price).toFixed(2)}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Selling Price</p>
                                    <p className="text-lg font-medium">${parseFloat(product.selling_price).toFixed(2)}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Min Price</p>
                                    <p className="text-lg">{product.min_price ? `$${parseFloat(product.min_price).toFixed(2)}` : 'Not set'}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Margin</p>
                                    <p className="text-sm">
                                        {((parseFloat(product.selling_price) - parseFloat(product.cost_price)) / parseFloat(product.selling_price) * 100).toFixed(1)}%
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Profit</p>
                                    <p className="text-sm">
                                        ${(parseFloat(product.selling_price) - parseFloat(product.cost_price)).toFixed(2)}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Box className="h-5 w-5" />
                                Stock Settings
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Reorder Level</p>
                                    <p className="text-lg">{product.reorder_level}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Reorder Quantity</p>
                                    <p className="text-lg">{product.reorder_quantity}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Layers className="h-5 w-5" />
                                System Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Created</p>
                                    <p className="text-sm">{new Date(product.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Updated</p>
                                    <p className="text-sm">{new Date(product.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Slug</p>
                                <p className="text-sm font-mono">{product.slug}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
