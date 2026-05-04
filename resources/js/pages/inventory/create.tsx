import { Head, Link, useForm } from '@inertiajs/react';
import { Package, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as inventoryIndex, store as inventoryStore } from '@/routes/inventory';
import type { InventoryFormProps } from '@/types';

export default function Create({ products, branches }: InventoryFormProps) {
    const { data, setData, post, processing, errors } = useForm({
        product_id: '',
        branch_id: '',
        quantity_on_hand: '0',
        quantity_reserved: '0',
        average_cost: '',
        last_count_date: '',
        last_received_date: '',
        is_active: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(inventoryStore.url());
    };

    return (
        <>
            <Head title="Create Inventory Record" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center gap-4">
                    <Link href={inventoryIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <Package className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold tracking-tight">Create Inventory Record</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Product & Location</CardTitle>
                            <CardDescription>Select the product and branch for this inventory record.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                                    {branch.name} ({branch.code})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.branch_id && <p className="text-sm text-destructive">{errors.branch_id}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Stock Information</CardTitle>
                            <CardDescription>Enter the stock quantities and cost information.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="quantity_on_hand">Quantity On Hand *</Label>
                                    <Input
                                        id="quantity_on_hand"
                                        type="number"
                                        min="0"
                                        value={data.quantity_on_hand}
                                        onChange={(e) => setData('quantity_on_hand', e.target.value)}
                                        placeholder="0"
                                    />
                                    {errors.quantity_on_hand && <p className="text-sm text-destructive">{errors.quantity_on_hand}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="quantity_reserved">Quantity Reserved</Label>
                                    <Input
                                        id="quantity_reserved"
                                        type="number"
                                        min="0"
                                        value={data.quantity_reserved}
                                        onChange={(e) => setData('quantity_reserved', e.target.value)}
                                        placeholder="0"
                                    />
                                    {errors.quantity_reserved && <p className="text-sm text-destructive">{errors.quantity_reserved}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="average_cost">Average Cost</Label>
                                    <Input
                                        id="average_cost"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.average_cost}
                                        onChange={(e) => setData('average_cost', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.average_cost && <p className="text-sm text-destructive">{errors.average_cost}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                            <CardDescription>Enter additional dates and settings.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="last_count_date">Last Count Date</Label>
                                    <Input
                                        id="last_count_date"
                                        type="date"
                                        value={data.last_count_date}
                                        onChange={(e) => setData('last_count_date', e.target.value)}
                                    />
                                    {errors.last_count_date && <p className="text-sm text-destructive">{errors.last_count_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="last_received_date">Last Received Date</Label>
                                    <Input
                                        id="last_received_date"
                                        type="date"
                                        value={data.last_received_date}
                                        onChange={(e) => setData('last_received_date', e.target.value)}
                                    />
                                    {errors.last_received_date && <p className="text-sm text-destructive">{errors.last_received_date}</p>}
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-6 pt-2">
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="font-normal cursor-pointer">
                                        Active
                                    </Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create Inventory Record'}
                        </Button>
                        <Link href={inventoryIndex()}>
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
