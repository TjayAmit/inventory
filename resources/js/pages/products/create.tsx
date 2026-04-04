import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import AppContentWrapper from '@/components/app-content-wrapper';

interface Category {
    id: number;
    name: string;
}

interface CreateProps {
    categories: Category[];
}

export default function ProductsCreate({ categories }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        product_code: '',
        barcode: '',
        description: '',
        price: '',
        cost_price: '',
        category_id: '',
        is_active: true,
        unit: 'pcs',
        weight: '',
        volume: '',
        brand: '',
        manufacturer: '',
        supplier: '',
        reorder_point: '10',
        max_stock: '1000',
        notes: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/products');
    };

    return (
        <>
            <Head title="Create Product" />
            <AppContentWrapper>
                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/products">Products</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>Create</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>
                
                {/* Page Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold tracking-tight">Create Product</h1>
                    <p className="text-muted-foreground mt-1">Add a new product to inventory</p>
                </div>

                {/* Card Content */}
                <div className="max-w-full">
                    <Card>
                        <CardContent className="pt-6">
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Product Name */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="name">Product Name *</Label>
                                        <Input
                                            id="name"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            className="h-10"
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-destructive">{errors.name}</p>
                                        )}
                                    </div>

                                    {/* Product Code */}
                                    <div className="space-y-2">
                                        <Label htmlFor="product_code">Product Code *</Label>
                                        <Input
                                            id="product_code"
                                            type="text"
                                            value={data.product_code}
                                            onChange={(e) => setData('product_code', e.target.value)}
                                            required
                                            className="h-10"
                                            placeholder="e.g., PROD-001"
                                        />
                                        {errors.product_code && (
                                            <p className="text-sm text-destructive">{errors.product_code}</p>
                                        )}
                                    </div>

                                    {/* Barcode */}
                                    <div className="space-y-2">
                                        <Label htmlFor="barcode">Barcode (EAN-13)</Label>
                                        <Input
                                            id="barcode"
                                            type="text"
                                            value={data.barcode}
                                            onChange={(e) => setData('barcode', e.target.value)}
                                            className="h-10"
                                            placeholder="Leave blank to auto-generate"
                                            maxLength={13}
                                        />
                                        {errors.barcode && (
                                            <p className="text-sm text-destructive">{errors.barcode}</p>
                                        )}
                                    </div>

                                    {/* Category */}
                                    <div className="space-y-2">
                                        <Label htmlFor="category_id">Category</Label>
                                        <Select
                                            value={data.category_id || undefined}
                                            onValueChange={(value) => setData('category_id', value)}
                                        >
                                            <SelectTrigger className="h-10">
                                                <SelectValue placeholder="Select category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((category) => (
                                                    <SelectItem key={category.id} value={String(category.id)}>
                                                        {category.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.category_id && (
                                            <p className="text-sm text-destructive">{errors.category_id}</p>
                                        )}
                                    </div>

                                    {/* Brand */}
                                    <div className="space-y-2">
                                        <Label htmlFor="brand">Brand</Label>
                                        <Input
                                            id="brand"
                                            type="text"
                                            value={data.brand}
                                            onChange={(e) => setData('brand', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.brand && (
                                            <p className="text-sm text-destructive">{errors.brand}</p>
                                        )}
                                    </div>

                                    {/* Price */}
                                    <div className="space-y-2">
                                        <Label htmlFor="price">Price (PHP) *</Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={data.price}
                                            onChange={(e) => setData('price', e.target.value)}
                                            required
                                            className="h-10"
                                        />
                                        {errors.price && (
                                            <p className="text-sm text-destructive">{errors.price}</p>
                                        )}
                                    </div>

                                    {/* Cost Price */}
                                    <div className="space-y-2">
                                        <Label htmlFor="cost_price">Cost Price (PHP)</Label>
                                        <Input
                                            id="cost_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={data.cost_price}
                                            onChange={(e) => setData('cost_price', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.cost_price && (
                                            <p className="text-sm text-destructive">{errors.cost_price}</p>
                                        )}
                                    </div>

                                    {/* Unit */}
                                    <div className="space-y-2">
                                        <Label htmlFor="unit">Unit *</Label>
                                        <Select
                                            value={data.unit}
                                            onValueChange={(value) => setData('unit', value)}
                                        >
                                            <SelectTrigger className="h-10">
                                                <SelectValue placeholder="Select unit" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="pcs">Pieces (pcs)</SelectItem>
                                                <SelectItem value="kg">Kilograms (kg)</SelectItem>
                                                <SelectItem value="g">Grams (g)</SelectItem>
                                                <SelectItem value="l">Liters (l)</SelectItem>
                                                <SelectItem value="ml">Milliliters (ml)</SelectItem>
                                                <SelectItem value="box">Box</SelectItem>
                                                <SelectItem value="pack">Pack</SelectItem>
                                                <SelectItem value="bottle">Bottle</SelectItem>
                                                <SelectItem value="can">Can</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.unit && (
                                            <p className="text-sm text-destructive">{errors.unit}</p>
                                        )}
                                    </div>

                                    {/* Reorder Point */}
                                    <div className="space-y-2">
                                        <Label htmlFor="reorder_point">Reorder Point</Label>
                                        <Input
                                            id="reorder_point"
                                            type="number"
                                            min="0"
                                            value={data.reorder_point}
                                            onChange={(e) => setData('reorder_point', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.reorder_point && (
                                            <p className="text-sm text-destructive">{errors.reorder_point}</p>
                                        )}
                                    </div>

                                    {/* Max Stock */}
                                    <div className="space-y-2">
                                        <Label htmlFor="max_stock">Max Stock</Label>
                                        <Input
                                            id="max_stock"
                                            type="number"
                                            min="0"
                                            value={data.max_stock}
                                            onChange={(e) => setData('max_stock', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.max_stock && (
                                            <p className="text-sm text-destructive">{errors.max_stock}</p>
                                        )}
                                    </div>

                                    {/* Weight */}
                                    <div className="space-y-2">
                                        <Label htmlFor="weight">Weight (kg)</Label>
                                        <Input
                                            id="weight"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            value={data.weight}
                                            onChange={(e) => setData('weight', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.weight && (
                                            <p className="text-sm text-destructive">{errors.weight}</p>
                                        )}
                                    </div>

                                    {/* Volume */}
                                    <div className="space-y-2">
                                        <Label htmlFor="volume">Volume (L)</Label>
                                        <Input
                                            id="volume"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            value={data.volume}
                                            onChange={(e) => setData('volume', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.volume && (
                                            <p className="text-sm text-destructive">{errors.volume}</p>
                                        )}
                                    </div>

                                    {/* Manufacturer */}
                                    <div className="space-y-2">
                                        <Label htmlFor="manufacturer">Manufacturer</Label>
                                        <Input
                                            id="manufacturer"
                                            type="text"
                                            value={data.manufacturer}
                                            onChange={(e) => setData('manufacturer', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.manufacturer && (
                                            <p className="text-sm text-destructive">{errors.manufacturer}</p>
                                        )}
                                    </div>

                                    {/* Supplier */}
                                    <div className="space-y-2">
                                        <Label htmlFor="supplier">Supplier</Label>
                                        <Input
                                            id="supplier"
                                            type="text"
                                            value={data.supplier}
                                            onChange={(e) => setData('supplier', e.target.value)}
                                            className="h-10"
                                        />
                                        {errors.supplier && (
                                            <p className="text-sm text-destructive">{errors.supplier}</p>
                                        )}
                                    </div>

                                    {/* Description */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                                            rows={3}
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-destructive">{errors.description}</p>
                                        )}
                                    </div>

                                    {/* Notes */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="notes">Notes</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('notes', e.target.value)}
                                            rows={2}
                                        />
                                        {errors.notes && (
                                            <p className="text-sm text-destructive">{errors.notes}</p>
                                        )}
                                    </div>

                                    {/* Checkboxes */}
                                    <div className="flex gap-6 md:col-span-2">
                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id="is_active"
                                                checked={data.is_active}
                                                onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                            />
                                            <Label htmlFor="is_active" className="cursor-pointer">
                                                Active
                                            </Label>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
                                    <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        Create Product
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </AppContentWrapper>
        </>
    );
}

ProductsCreate.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: '/products',
        },
    ],
};
