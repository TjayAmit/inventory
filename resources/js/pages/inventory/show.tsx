import { Head, Link } from '@inertiajs/react';
import { Package, ArrowLeft, Building2, Box, DollarSign, Calendar, Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as inventoryIndex, edit as inventoryEdit } from '@/routes/inventory';
import type { InventoryShowProps } from '@/types';

export default function Show({ inventory }: InventoryShowProps) {
    const getStockStatus = () => {
        if (inventory.quantity_on_hand <= 0) {
            return <Badge variant="destructive" className="text-sm px-3 py-1">Out of Stock</Badge>;
        }
        if (inventory.quantity_available <= 10) {
            return <Badge variant="outline" className="text-sm px-3 py-1 border-orange-400 text-orange-600">Low Stock</Badge>;
        }
        return <Badge variant="default" className="text-sm px-3 py-1">In Stock</Badge>;
    };

    return (
        <>
            <Head title={`Inventory - ${inventory.product?.name || 'Details'}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={inventoryIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <Package className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">
                                    {inventory.product?.name || 'Inventory Details'}
                                </h1>
                                <p className="text-sm text-muted-foreground">
                                    SKU: {inventory.product?.sku || '-'}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={inventoryEdit(inventory.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Record
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {getStockStatus()}
                    {!inventory.is_active && (
                        <Badge variant="secondary" className="text-sm px-3 py-1">Inactive</Badge>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Box className="h-5 w-5" />
                                Stock Levels
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">On Hand</p>
                                    <p className="text-2xl font-bold">{inventory.quantity_on_hand}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Reserved</p>
                                    <p className="text-2xl font-bold">{inventory.quantity_reserved}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Available</p>
                                    <p className="text-2xl font-bold text-green-600">{inventory.quantity_available}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building2 className="h-5 w-5" />
                                Location
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Branch</p>
                                <p className="text-lg">{inventory.branch?.name || '-'}</p>
                                <p className="text-sm text-muted-foreground">{inventory.branch?.code || ''}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Cost Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Average Cost</p>
                                    <p className="text-lg">
                                        {inventory.average_cost
                                            ? `$${parseFloat(inventory.average_cost).toFixed(2)}`
                                            : 'Not set'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Stock Value</p>
                                    <p className="text-lg">
                                        {inventory.average_cost
                                            ? `$${(parseFloat(inventory.average_cost) * inventory.quantity_on_hand).toFixed(2)}`
                                            : 'Not calculated'}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Important Dates
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Last Count Date</p>
                                    <p className="text-sm">
                                        {inventory.last_count_date
                                            ? new Date(inventory.last_count_date).toLocaleDateString()
                                            : 'Never counted'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Last Received Date</p>
                                    <p className="text-sm">
                                        {inventory.last_received_date
                                            ? new Date(inventory.last_received_date).toLocaleDateString()
                                            : 'Never received'}
                                    </p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Created</p>
                                    <p className="text-sm">{new Date(inventory.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Updated</p>
                                    <p className="text-sm">{new Date(inventory.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
