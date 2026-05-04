import { Head, router } from '@inertiajs/react';
import { MoreVertical, Pencil, Trash2, Eye, Users } from 'lucide-react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import { DataTable, DataTableColumn, DataTablePagination } from '@/components/data-table';
import {
    index as users,
    create as usersCreate,
    show as usersShow,
    edit as usersEdit,
    destroy as usersDestroy,
} from '@/routes/users';
import type { UserIndexProps, User } from '@/types';
import AppLayout from '@/layouts/app-layout';

const MODULE_TITLE = 'Users';

export default function Index({ data, filters }: UserIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const searchTimeout = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            users(),
            { search, per_page: perPage, ...params },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleSearchChange = (value: string) => {
        setSearch(value);
        clearTimeout(searchTimeout.current);
        searchTimeout.current = setTimeout(() => {
            navigate({ search: value, page: 1 });
        }, 350);
    };

    const handlePerPageChange = (value: number) => {
        setPerPage(value);
        navigate({ per_page: value, page: 1 });
    };

    const handleDelete = () => {
        if (!deleteId) return;
        setIsDeleting(true);
        router.delete(usersDestroy(deleteId), {
            onFinish: () => {
                setIsDeleting(false);
                setDeleteId(null);
            },
        });
    };

    const columns: DataTableColumn<User>[] = [
        {
            key: 'name',
            header: 'Name',
            cell: (item) => (
                <span className="text-sm font-medium text-foreground">
                    {item.name}
                </span>
            ),
        },
        {
            key: 'email',
            header: 'Email',
            cell: (item) => (
                <span className="text-sm text-muted-foreground">
                    {item.email}
                </span>
            ),
        },
        {
            key: 'created_at',
            header: 'Created At',
            cell: (item) => (
                <span className="text-sm text-muted-foreground">
                    {item.created_at}
                </span>
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
            icon: <Eye className="mr-2 h-4 w-4" />,
            label: 'View',
            onClick: (item: User) => router.get(usersShow(item.id)),
        },
        {
            key: 'edit',
            icon: <Pencil className="mr-2 h-4 w-4" />,
            label: 'Edit',
            onClick: (item: User) => router.get(usersEdit(item.id)),
        },
        {
            key: 'delete',
            icon: <Trash2 className="mr-2 h-4 w-4" />,
            label: 'Delete',
            variant: 'destructive' as const,
            onClick: (item: User) => setDeleteId(item.id),
        },
    ];

    const userToDelete = data.data.find((u) => u.id === deleteId);

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<User>
                    title={MODULE_TITLE}
                    description="Manage your branch locations"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    createHref={usersCreate().url}
                    createLabel={`New ${MODULE_TITLE.slice(0, -1)}`}
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
                onClose={() => setDeleteId(null)}
                onConfirm={handleDelete}
                title={`Delete ${MODULE_TITLE.slice(0, -1)}`}
                description="This action cannot be undone."
                itemName={userToDelete?.name}
            />
        </>
    );
}

Index.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: MODULE_TITLE, href: users() }]}>
        {page}
    </AppLayout>
);
