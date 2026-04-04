import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Plus, Edit, Eye, Trash2, Barcode, ToggleLeft, ToggleRight } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTableColumn, DataTableAction, useDebouncedValue, DataTableCells } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import { Badge } from '@/components/ui/badge';
import AppContentWrapper from '@/components/app-content-wrapper';

interface Category {
    id: number;
    name: string;
}

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
    brand: string | null;
    supplier: string | null;
    reorderPoint: number;
    formattedPrice: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ProductsProps {
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        category?: string;
        status?: string;
    };
    categories: Category[];
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
        manage: boolean;
    };
}

export default function ProductsIndex({ products, filters, categories, can }: ProductsProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [categoryFilter, setCategoryFilter] = useState(filters.category || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const debouncedSearch = useDebouncedValue(search, 500);

    // Apply filters when they change
    React.useEffect(() => {
        router.get('/products', { 
            search: debouncedSearch, 
            category: categoryFilter,
            status: statusFilter 
        }, { preserveState: true, replace: true });
    }, [debouncedSearch, categoryFilter, statusFilter]);

    const [deleteDialog, setDeleteDialog] = useState<{
        isOpen: boolean;
        product: Product | null;
    }>({ isOpen: false, product: null });

    const openDeleteDialog = (product: Product) => {
        setDeleteDialog({ isOpen: true, product });
    };

    const closeDeleteDialog = () => {
        setDeleteDialog({ isOpen: false, product: null });
    };

    const confirmDelete = () => {
        if (deleteDialog.product) {
            router.delete(`/products/${deleteDialog.product.id}`);
            closeDeleteDialog();
        }
    };

    const toggleStatus = (product: Product) => {
        router.put(`/products/${product.id}/toggle-status`, {}, { preserveState: true });
    };

    const generateBarcode = (product: Product) => {
        router.put(`/products/${product.id}/generate-barcode`, {}, { preserveState: true });
    };

    // Define columns for the products table
    const columns: DataTableColumn<Product>[] = [
        {
            key: 'name',
            header: 'Name',
            cell: (product) => (
                <div className="flex flex-col">
                    <span className="font-medium text-foreground">{product.name}</span>
                    {product.productCode && (
                        <span className="text-xs text-muted-foreground">{product.productCode}</span>
                    )}
                </div>
            ),
        },
        {
            key: 'categoryName',
            header: 'Category',
            cell: (product) => product.categoryName ? (
                <span className="text-muted-foreground">{product.categoryName}</span>
            ) : (
                <span className="text-muted-foreground italic">Uncategorized</span>
            ),
        },
        {
            key: 'barcode',
            header: 'Barcode',
            cell: (product) => product.barcode ? (
                <span className="font-mono text-sm text-muted-foreground">{product.barcode}</span>
            ) : (
                <span className="text-muted-foreground italic text-sm">No barcode</span>
            ),
        },
        {
            key: 'price',
            header: 'Price',
            cell: (product) => (
                <span className="font-medium text-foreground">{product.formattedPrice}</span>
            ),
        },
        {
            key: 'isActive',
            header: 'Status',
            cell: (product) => (
                <Badge variant={product.isActive ? 'default' : 'secondary'}>
                    {product.isActive ? 'Active' : 'Inactive'}
                </Badge>
            ),
        },
    ];

    // Define actions for the products table
    const actions: DataTableAction<Product>[] = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: 'View',
            href: (product) => `/products/${product.id}`,
            variant: 'ghost',
        },
        {
            key: 'edit',
            icon: <Edit className="w-4 h-4" />,
            label: 'Edit',
            href: (product) => `/products/${product.id}/edit`,
            variant: 'ghost',
            visible: () => can.edit,
        },
        {
            key: 'barcode',
            icon: <Barcode className="w-4 h-4" />,
            label: 'Generate Barcode',
            onClick: generateBarcode,
            variant: 'ghost',
            visible: (product) => can.manage && !product.barcode,
        },
        {
            key: 'toggle',
            icon: <ToggleRight className="w-4 h-4" />,
            label: 'Toggle Status',
            onClick: toggleStatus,
            variant: 'ghost',
            visible: () => can.manage,
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: openDeleteDialog,
            variant: 'ghost',
            visible: () => can.delete,
        },
    ];

    // Category filter component
    const categoryFilterComponent = (
        <div className="flex gap-2">
            <Select
                value={categoryFilter || undefined}
                onValueChange={(value) => setCategoryFilter(value === 'all' ? '' : value)}
            >
                <SelectTrigger className="w-44 h-10 bg-muted/50 border-border hover:bg-muted focus:bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg transition-all duration-200">
                    <SelectValue placeholder="Filter by category" />
                </SelectTrigger>
                <SelectContent className="bg-popover border-border shadow-lg rounded-lg">
                    <SelectItem value="all" className="hover:bg-muted/50 cursor-pointer">
                        All Categories
                    </SelectItem>
                    {categories.map((category) => (
                        <SelectItem
                            key={category.id}
                            value={String(category.id)}
                            className="hover:bg-muted/50 cursor-pointer"
                        >
                            {category.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <Select
                value={statusFilter || undefined}
                onValueChange={(value) => setStatusFilter(value === 'all' ? '' : value)}
            >
                <SelectTrigger className="w-36 h-10 bg-muted/50 border-border hover:bg-muted focus:bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg transition-all duration-200">
                    <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent className="bg-popover border-border shadow-lg rounded-lg">
                    <SelectItem value="all" className="hover:bg-muted/50 cursor-pointer">
                        All Status
                    </SelectItem>
                    <SelectItem value="active" className="hover:bg-muted/50 cursor-pointer">
                        Active
                    </SelectItem>
                    <SelectItem value="inactive" className="hover:bg-muted/50 cursor-pointer">
                        Inactive
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    );

    return (
        <>
            <Head title="Products" />
            <AppContentWrapper>
                <DataTable
                    title="Products"
                    description="Manage product inventory, barcodes, and pricing"
                    data={products.data}
                    columns={columns}
                    pagination={{
                        current_page: products.current_page,
                        last_page: products.last_page,
                        per_page: products.per_page,
                        total: products.total,
                        links: products.links,
                    }}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Search products..."
                    filters={categoryFilterComponent}
                    actions={actions}
                    createHref={can.create ? "/products/create" : undefined}
                    createLabel="Add Product"
                    emptyTitle="No products found"
                    emptyDescription="Get started by creating a new product."
                />
            </AppContentWrapper>

            <DeleteConfirmDialog
                isOpen={deleteDialog.isOpen}
                onClose={closeDeleteDialog}
                onConfirm={confirmDelete}
                itemName={deleteDialog.product?.name}
                description="This action cannot be undone. The product will be permanently removed from the system."
            />
        </>
    );
}

ProductsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: '/products',
        },
    ],
};
