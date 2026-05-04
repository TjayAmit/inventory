import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2, Package } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as inventoryIndex,
    create as inventoryCreate,
    show as inventoryShow,
    edit as inventoryEdit,
    destroy as inventoryDestroy,
} from '@/routes/inventory';
import type { InventoryIndexProps, Inventory } from '@/types';

const MODULE_TITLE = 'Inventory';

export default function Index({ data, filters }: InventoryIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteName, setDeleteName] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            inventoryIndex(),
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
        router.delete(inventoryDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteName('');
            },
        });
    };

    const getStockStatus = (item: Inventory) => {
        if (item.quantity_on_hand <= 0) {
            return <Badge variant="destructive" className="text-xs">Out of Stock</Badge>;
        }
        if (item.quantity_available <= 10) {
            return <Badge variant="outline" className="text-xs border-orange-400 text-orange-600">Low Stock</Badge>;
        }
        return <Badge variant="default" className="text-xs">In Stock</Badge>;
    };

    const columns = [
        {
            key: 'product',
            header: 'Product',
            cell: (item: Inventory) => (
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
            key: 'branch',
            header: 'Branch',
            cell: (item: Inventory) => (
                <span className="text-sm text-foreground">
                    {item.branch?.name || '-'}
                </span>
            ),
        },
        {
            key: 'quantity',
            header: 'Stock Levels',
            cell: (item: Inventory) => (
                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">On Hand:</span>
                        <span className="font-medium">{item.quantity_on_hand}</span>
                    </div>
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        <span>Reserved: {item.quantity_reserved}</span>
                        <span>|</span>
                        <span>Available: {item.quantity_available}</span>
                    </div>
                </div>
            ),
        },
        {
            key: 'cost',
            header: 'Avg. Cost',
            cell: (item: Inventory) => (
                <span className="text-sm text-foreground">
                    {item.average_cost ? `$${parseFloat(item.average_cost).toFixed(2)}` : '-'}
                </span>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (item: Inventory) => (
                <div className="flex flex-col gap-1">
                    {getStockStatus(item)}
                    {!item.is_active && (
                        <Badge variant="secondary" className="text-xs">Inactive</Badge>
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
            onClick: (item: Inventory) => inventoryShow(item.id)
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (item: Inventory) => inventoryEdit(item.id)
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (item: Inventory) => {
                setDeleteId(item.id);
                setDeleteName(item.product?.name || 'this inventory record');
            }
        },
    ];

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Inventory>
                    title={MODULE_TITLE}
                    description="Manage product stock across branches"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={inventoryCreate().url}
                    createLabel="New Inventory Record"
                    actions={actions}
                    onDelete={(item) => {
                        setDeleteId(item.id);
                        setDeleteName(item.product?.name || 'this inventory record');
                    }}
                    deleteConfirmation={{
                        title: `Delete ${MODULE_TITLE.slice(0, -1)} Record`,
                        description: (item) => `Are you sure you want to delete inventory record for ${item.product?.name || 'this item'}? This action cannot be undone.`,
                    }}
                    pageSizeOptions={[10, 25, 50, 100]}
                    onPageSizeChange={handlePerPageChange}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle={`No ${MODULE_TITLE.toLowerCase()} records found`}
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
                title="Delete Inventory Record"
                itemName={deleteName}
                description="This will permanently delete this inventory record. This action cannot be undone."
            />
        </>
    );
}
