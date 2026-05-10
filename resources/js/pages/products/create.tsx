import { Head, Link, useForm } from '@inertiajs/react';
import { Package, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as productsIndex, store as productsStore } from '@/routes/products';
import type { ProductFormProps } from '@/types/product';

export default function Create({ categories }: ProductFormProps) {
    const { data, setData, post, processing, errors } = useForm({
        sku: '',
        barcode: '',
        name: '',
        slug: '',
        description: '',
        category_id: '',
        brand: '',
        unit: 'pieces',
        cost_price: '',
        selling_price: '',
        min_price: '',
        reorder_level: '10',
        reorder_quantity: '50',
        is_active: true,
        is_taxable: true,
        is_trackable: true,
        image_urls: [],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(productsStore.url());
    };

    return (
        <>
            <Head title="Create Product" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-5xl mx-auto">
                <div className="flex items-center gap-4">
                    <Link href={productsIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <Package className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold tracking-tight">Create Product</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Enter the product identification details.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="sku">SKU *</Label>
                                    <Input
                                        id="sku"
                                        value={data.sku}
                                        onChange={(e) => setData('sku', e.target.value)}
                                        placeholder="e.g., PROD-001"
                                    />
                                    {errors.sku && <p className="text-sm text-destructive">{errors.sku}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="barcode">Barcode</Label>
                                    <Input
                                        id="barcode"
                                        value={data.barcode}
                                        onChange={(e) => setData('barcode', e.target.value)}
                                        placeholder="e.g., 123456789012"
                                    />
                                    {errors.barcode && <p className="text-sm text-destructive">{errors.barcode}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Product Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => {
                                            setData('name', e.target.value);
                                            setData('slug', e.target.value.toLowerCase().replace(/\s+/g, '-'));
                                        }}
                                        placeholder="e.g., Wireless Mouse"
                                    />
                                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="slug">Slug *</Label>
                                    <Input
                                        id="slug"
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="e.g., wireless-mouse"
                                    />
                                    {errors.slug && <p className="text-sm text-destructive">{errors.slug}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Input
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Product description..."
                                />
                                {errors.description && <p className="text-sm text-destructive">{errors.description}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="category">Category</Label>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) => setData('category_id', value)}
                                    >
                                        <SelectTrigger id="category">
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={String(category.id)}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id && <p className="text-sm text-destructive">{errors.category_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="brand">Brand</Label>
                                    <Input
                                        id="brand"
                                        value={data.brand}
                                        onChange={(e) => setData('brand', e.target.value)}
                                        placeholder="e.g., Logitech"
                                    />
                                    {errors.brand && <p className="text-sm text-destructive">{errors.brand}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="unit">Unit *</Label>
                                <Input
                                    id="unit"
                                    value={data.unit}
                                    onChange={(e) => setData('unit', e.target.value)}
                                    placeholder="e.g., pieces, kg, liters"
                                />
                                {errors.unit && <p className="text-sm text-destructive">{errors.unit}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing</CardTitle>
                            <CardDescription>Set the product pricing information.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="cost_price">Cost Price *</Label>
                                    <Input
                                        id="cost_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.cost_price}
                                        onChange={(e) => setData('cost_price', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.cost_price && <p className="text-sm text-destructive">{errors.cost_price}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="selling_price">Selling Price *</Label>
                                    <Input
                                        id="selling_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.selling_price}
                                        onChange={(e) => setData('selling_price', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.selling_price && <p className="text-sm text-destructive">{errors.selling_price}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="min_price">Minimum Price</Label>
                                    <Input
                                        id="min_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.min_price}
                                        onChange={(e) => setData('min_price', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.min_price && <p className="text-sm text-destructive">{errors.min_price}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Stock Settings</CardTitle>
                            <CardDescription>Configure inventory management settings.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="reorder_level">Reorder Level *</Label>
                                    <Input
                                        id="reorder_level"
                                        type="number"
                                        min="0"
                                        value={data.reorder_level}
                                        onChange={(e) => setData('reorder_level', e.target.value)}
                                        placeholder="10"
                                    />
                                    {errors.reorder_level && <p className="text-sm text-destructive">{errors.reorder_level}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="reorder_quantity">Reorder Quantity *</Label>
                                    <Input
                                        id="reorder_quantity"
                                        type="number"
                                        min="0"
                                        value={data.reorder_quantity}
                                        onChange={(e) => setData('reorder_quantity', e.target.value)}
                                        placeholder="50"
                                    />
                                    {errors.reorder_quantity && <p className="text-sm text-destructive">{errors.reorder_quantity}</p>}
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

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_taxable"
                                        checked={data.is_taxable}
                                        onCheckedChange={(checked) => setData('is_taxable', checked as boolean)}
                                    />
                                    <Label htmlFor="is_taxable" className="font-normal cursor-pointer">
                                        Taxable
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_trackable"
                                        checked={data.is_trackable}
                                        onCheckedChange={(checked) => setData('is_trackable', checked as boolean)}
                                    />
                                    <Label htmlFor="is_trackable" className="font-normal cursor-pointer">
                                        Track Inventory
                                    </Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create Product'}
                        </Button>
                        <Link href={productsIndex()}>
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
