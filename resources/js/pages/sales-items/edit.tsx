import { Head, Link, useForm } from '@inertiajs/react';
import { ShoppingCart, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as salesItemsIndex, update as salesItemsUpdate } from '@/routes/sales-items';
import type { SalesItemEditProps } from '@/types/sales-item';

export default function Edit({ salesItem, salesOrders, products, inventoryBatches }: SalesItemEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        sales_order_id: String(salesItem.sales_order_id),
        product_id: String(salesItem.product_id),
        inventory_batch_id: salesItem.inventory_batch_id ? String(salesItem.inventory_batch_id) : '',
        quantity: String(salesItem.quantity),
        unit_price: salesItem.unit_price,
        unit_cost: salesItem.unit_cost || '',
        discount_amount: salesItem.discount_amount || '0',
        tax_amount: salesItem.tax_amount || '0',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(salesItemsUpdate.url(salesItem.id));
    };

    return (
        <>
            <Head title="Edit Sales Item" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center gap-4">
                    <Link href={salesItemsIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <ShoppingCart className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold tracking-tight">Edit Sales Item</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Order & Product</CardTitle>
                            <CardDescription>Update the sales order and product for this item.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="sales_order">Sales Order *</Label>
                                    <Select
                                        value={data.sales_order_id}
                                        onValueChange={(value) => setData('sales_order_id', value)}
                                    >
                                        <SelectTrigger id="sales_order">
                                            <SelectValue placeholder="Select an order" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {salesOrders.map((order) => (
                                                <SelectItem key={order.id} value={String(order.id)}>
                                                    {order.order_number}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.sales_order_id && <p className="text-sm text-destructive">{errors.sales_order_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="product">Product *</Label>
                                    <Select
                                        value={data.product_id}
                                        onValueChange={(value) => setData('product_id', value)}
                                    >
                                        <SelectTrigger id="product">
                                            <SelectValue placeholder="Select a product" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {products.map((product) => (
                                                <SelectItem key={product.id} value={String(product.id)}>
                                                    {product.name} ({product.sku})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.product_id && <p className="text-sm text-destructive">{errors.product_id}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="inventory_batch">Inventory Batch</Label>
                                <Select
                                    value={data.inventory_batch_id}
                                    onValueChange={(value) => setData('inventory_batch_id', value)}
                                >
                                    <SelectTrigger id="inventory_batch">
                                        <SelectValue placeholder="Select a batch (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">None</SelectItem>
                                        {inventoryBatches.map((batch) => (
                                            <SelectItem key={batch.id} value={String(batch.id)}>
                                                {batch.batch_number}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.inventory_batch_id && <p className="text-sm text-destructive">{errors.inventory_batch_id}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing & Quantity</CardTitle>
                            <CardDescription>Update quantity, pricing, and cost information.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="quantity">Quantity *</Label>
                                    <Input
                                        id="quantity"
                                        type="number"
                                        min="1"
                                        value={data.quantity}
                                        onChange={(e) => setData('quantity', e.target.value)}
                                        placeholder="1"
                                    />
                                    {errors.quantity && <p className="text-sm text-destructive">{errors.quantity}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="unit_price">Unit Price *</Label>
                                    <Input
                                        id="unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.unit_price}
                                        onChange={(e) => setData('unit_price', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.unit_price && <p className="text-sm text-destructive">{errors.unit_price}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="unit_cost">Unit Cost</Label>
                                    <Input
                                        id="unit_cost"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.unit_cost}
                                        onChange={(e) => setData('unit_cost', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.unit_cost && <p className="text-sm text-destructive">{errors.unit_cost}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="discount_amount">Discount Amount</Label>
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

                                <div className="space-y-2">
                                    <Label htmlFor="tax_amount">Tax Amount</Label>
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
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Link href={salesItemsIndex()}>
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
