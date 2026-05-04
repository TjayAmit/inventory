import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as suppliersIndex,
    create as suppliersCreate,
    show as suppliersShow,
    edit as suppliersEdit,
    destroy as suppliersDestroy,
} from '@/routes/suppliers';
import type { SupplierIndexProps, Supplier } from '@/types';

const MODULE_TITLE = 'Suppliers';

export default function Index({ data, filters }: SupplierIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteName, setDeleteName] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            suppliersIndex(),
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
        router.delete(suppliersDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteName('');
            },
        });
    };

    const columns = [
        {
            key: 'supplier_code',
            header: 'Code',
            cell: (supplier: Supplier) => (
                <span className="text-sm font-medium text-foreground">
                    {supplier.supplier_code}
                </span>
            ),
        },
        {
            key: 'name',
            header: 'Name',
            cell: (supplier: Supplier) => (
                <span className="text-sm text-foreground">
                    {supplier.name}
                </span>
            ),
        },
        {
            key: 'contact_person',
            header: 'Contact Person',
            cell: (supplier: Supplier) => (
                <span className="text-sm text-foreground">
                    {supplier.contact_person || '-'}
                </span>
            ),
        },
        {
            key: 'city',
            header: 'City',
            cell: (supplier: Supplier) => (
                <span className="text-sm text-foreground">
                    {supplier.city || '-'}
                </span>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (supplier: Supplier) => (
                <Badge variant={supplier.is_active ? 'default' : 'destructive'} className="text-xs">
                    {supplier.is_active ? 'Active' : 'Inactive'}
                </Badge>
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
            onClick: (supplier: Supplier) => suppliersShow(supplier.id)
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (supplier: Supplier) => suppliersEdit(supplier.id)
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (supplier: Supplier) => {
                setDeleteId(supplier.id);
                setDeleteName(supplier.name);
            }
        },
    ];

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Supplier>
                    title={MODULE_TITLE}
                    description="Manage your suppliers and vendors"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={suppliersCreate().url}
                    createLabel="New Supplier"
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
                title="Delete Supplier"
                itemName={deleteName}
                description="This will permanently delete this supplier and all associated data. This action cannot be undone."
            />
        </>
    );
}
