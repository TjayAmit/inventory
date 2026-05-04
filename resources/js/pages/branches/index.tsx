import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as branchesIndex,
    create as branchesCreate,
    show as branchesShow,
    edit as branchesEdit,
    destroy as branchesDestroy,
} from '@/routes/branches';
import type { BranchIndexProps, Branch } from '@/types';

const MODULE_TITLE = 'Users';

export default function Index({ data, filters }: BranchIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteName, setDeleteName] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            branchesIndex(),
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
        router.delete(branchesDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteName('');
            },
        });
    };

    const columns = [
        {
            key: 'code',
            header: 'Code',
            cell: (branch: Branch) => (
                <span className="text-sm font-medium text-foreground">
                    {branch.code}
                </span>
            ),
        },
        {
            key: 'name',
            header: 'Name',
            cell: (branch: Branch) => (
                <span className="text-sm text-foreground">
                    {branch.name}
                </span>
            ),
        },
        {
            key: 'city',
            header: 'City',
            cell: (branch: Branch) => (
                <span className="text-sm text-foreground">
                    {branch.city || '-'}
                </span>
            ),
        },
        {
            key: 'manager',
            header: 'Manager',
            cell: (branch: Branch) => (
                <span className="text-sm text-foreground">
                    {branch.manager?.name || '-'}
                </span>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (branch: Branch) => (
                <div className="flex gap-1">
                    {branch.is_active ? (
                        <Badge variant="default" className="text-xs">Active</Badge>
                    ) : (
                        <Badge variant="destructive" className="text-xs">Inactive</Badge>
                    )}
                    {branch.is_main_branch && (
                        <Badge variant="outline" className="text-xs">Main</Badge>
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
            onClick: (branch: Branch) => branchesShow(branch.id)
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (branch: Branch) => branchesEdit(branch.id)
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (branch: Branch) => setDeleteId(branch.id)
        },
    ];

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Branch>
                    title={MODULE_TITLE}
                    description="Manage your branch locations"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={branchesCreate().url}
                    createLabel="New Branch"
                    actions={actions}
                    onDelete={(item) => setDeleteId(item.id)}
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
                title="Delete Branch"
                itemName={deleteName}
                description="This will permanently delete this branch and all associated data. This action cannot be undone."
            />
        </>
    );
}
