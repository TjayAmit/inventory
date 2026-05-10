import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2, ShoppingCart } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as salesItemsIndex,
    create as salesItemsCreate,
    show as salesItemsShow,
    edit as salesItemsEdit,
    destroy as salesItemsDestroy,
} from '@/routes/sales-items';
import type { SalesItemIndexProps, SalesItem } from '@/types/sales-item';

const MODULE_TITLE = 'Sales Items';

export default function Index({ data, filters }: SalesItemIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteInfo, setDeleteInfo] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            salesItemsIndex(),
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
        router.delete(salesItemsDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteInfo('');
            },
        });
    };

    const getProfitBadge = (item: SalesItem) => {
        const profit = parseFloat(item.profit || '0');
        if (profit > 0) {
            return <Badge variant="default" className="text-xs">+${profit.toFixed(2)}</Badge>;
        } else if (profit < 0) {
            return <Badge variant="destructive" className="text-xs">-${Math.abs(profit).toFixed(2)}</Badge>;
        }
        return <Badge variant="outline" className="text-xs">$0.00</Badge>;
    };

    const columns = [
        {
            key: 'order',
            header: 'Order #',
            cell: (item: SalesItem) => (
                <span className="text-sm font-medium text-foreground font-mono">
                    {item.salesOrder?.order_number || '-'}
                </span>
            ),
        },
        {
            key: 'product',
            header: 'Product',
            cell: (item: SalesItem) => (
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-foreground">
                        {item.product?.name || '-'}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        {item.product?.sku || '-'}
                    </span>
                </div>
            ),
        },
        {
            key: 'quantity',
            header: 'Qty',
            cell: (item: SalesItem) => (
                <span className="text-sm text-foreground">
                    {item.quantity}
                </span>
            ),
        },
        {
            key: 'price',
            header: 'Price',
            cell: (item: SalesItem) => (
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-foreground">
                        ${parseFloat(item.unit_price).toFixed(2)}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        Total: ${parseFloat(item.total_price).toFixed(2)}
                    </span>
                </div>
            ),
        },
        {
            key: 'cost',
            header: 'Cost & Profit',
            cell: (item: SalesItem) => (
                <div className="flex flex-col gap-1">
                    <span className="text-xs text-muted-foreground">
                        Cost: ${item.unit_cost ? parseFloat(item.unit_cost).toFixed(2) : '-'}
                    </span>
                    {getProfitBadge(item)}
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
            onClick: (item: SalesItem) => salesItemsShow(item.id)
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (item: SalesItem) => salesItemsEdit(item.id)
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (item: SalesItem) => {
                setDeleteId(item.id);
                setDeleteInfo(`${item.product?.name || 'this item'} from ${item.salesOrder?.order_number || 'order'}`);
            }
        },
    ];

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<SalesItem>
                    title={MODULE_TITLE}
                    description="Manage sales order line items"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={salesItemsCreate().url}
                    createLabel="New Sales Item"
                    actions={actions}
                    onDelete={(item) => {
                        setDeleteId(item.id);
                        setDeleteInfo(`${item.product?.name || 'this item'} from ${item.salesOrder?.order_number || 'order'}`);
                    }}
                    deleteConfirmation={{
                        title: `Delete Sales Item`,
                        description: (item) => `Are you sure you want to delete ${item.product?.name || 'this item'}? This action cannot be undone.`,
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
                    setDeleteInfo('');
                }}
                onConfirm={handleDelete}
                title="Delete Sales Item"
                itemName={deleteInfo}
                description="This will permanently delete this sales item. This action cannot be undone."
            />
        </>
    );
}
