import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2, Package } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as productsIndex,
    create as productsCreate,
    show as productsShow,
    edit as productsEdit,
    destroy as productsDestroy,
} from '@/routes/products';
import type { ProductIndexProps, Product } from '@/types/product';

const MODULE_TITLE = 'Products';

export default function Index({ data, filters }: ProductIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteName, setDeleteName] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            productsIndex(),
            { search, per_page: perPage, ...params },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleSearchChange = (value: string) => {
        setSearch(value);
        navigate({ search: value, page: 1 });
    };

    const handlePerPageChange = (value: number) => {
        setPerPage(value);
        navigate({ per_page: value, page: 1 });
    };

    const handleDelete = () => {
        if (!deleteId) return;
        router.delete(productsDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteName('');
            },
        });
    };

    const columns = [
        {
            key: 'sku',
            header: 'SKU',
            cell: (product: Product) => (
                <span className="text-sm font-medium text-foreground">
                    {product.sku}
                </span>
            ),
        },
        {
            key: 'name',
            header: 'Name',
            cell: (product: Product) => (
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-foreground">
                        {product.name}
                    </span>
                    {product.brand && (
                        <span className="text-xs text-muted-foreground">
                            {product.brand}
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'category',
            header: 'Category',
            cell: (product: Product) => (
                <span className="text-sm text-foreground">
                    {product.category?.name || '-'}
                </span>
            ),
        },
        {
            key: 'price',
            header: 'Price',
            cell: (product: Product) => (
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-foreground">
                        ${parseFloat(product.selling_price).toFixed(2)}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        Cost: ${parseFloat(product.cost_price).toFixed(2)}
                    </span>
                </div>
            ),
        },
        {
            key: 'stock',
            header: 'Stock',
            cell: (product: Product) => (
                <span className="text-sm text-foreground">
                    {product.reorder_level} / {product.reorder_quantity}
                </span>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (product: Product) => (
                <div className="flex gap-1">
                    {product.is_active ? (
                        <Badge variant="default" className="text-xs">Active</Badge>
                    ) : (
                        <Badge variant="destructive" className="text-xs">Inactive</Badge>
                    )}
                    {product.is_trackable && (
                        <Badge variant="outline" className="text-xs">Trackable</Badge>
                    )}
                </div>
            ),
        },
    ];

    const pagination: DataTablePagination = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total,
        links: [],
    };

    const actions = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: 'View',
            onClick: (product: Product) => productsShow(product.id)
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (product: Product) => productsEdit(product.id)
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (product: Product) => {
                setDeleteId(product.id);
                setDeleteName(product.name);
            }
        },
    ];

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Product>
                    title={MODULE_TITLE}
                    description="Manage your product catalog"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={productsCreate().url}
                    createLabel="New Product"
                    actions={actions}
                    onDelete={(item) => {
                        setDeleteId(item.id);
                        setDeleteName(item.name);
                    }}
                    deleteConfirmation={{
                        title: `Delete ${MODULE_TITLE.slice(0, -1)}`,
                        description: (item) => `Are you sure you want to delete ${item.name}? This action cannot be undone.`,
                    }}
                    pageSizeOptions={[10, 25, 50, 100]}
                    onPageSizeChange={handlePerPageChange}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle={`No ${MODULE_TITLE.toLowerCase()} found`}
                    emptyDescription={search ? 'Try a different search or clear the filter.' : undefined}
                />
            </div>

            <DeleteConfirmDialog
                isOpen={!!deleteId}
                onClose={() => {
                    setDeleteId(null);
                    setDeleteName('');
                }}
                onConfirm={handleDelete}
                title="Delete Product"
                itemName={deleteName}
                description="This will permanently delete this product and all associated data. This action cannot be undone."
            />
        </>
    );
}
