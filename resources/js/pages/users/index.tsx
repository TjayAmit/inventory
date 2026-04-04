import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Eye, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTableColumn, DataTableAction, useDebouncedValue, DataTableCells } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import AppContentWrapper from '@/components/app-content-wrapper';

interface User {
    id: number;
    name: string;
    email: string;
    roles: Array<{
        id: number;
        name: string;
    }>;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        role?: string;
    };
    roles: Array<{
        id: number;
        name: string;
    }>;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            roles: Array<{
                id: number;
                name: string;
            }>;
        };
    };
}

export default function UsersIndex({ users, filters, roles, auth }: UsersProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');
    const debouncedSearch = useDebouncedValue(search, 500);

    const isAdmin = (user: User) => user.roles.some(role => role.name === 'admin');
    const canDelete = (user: User) => !isAdmin(user);

    // Apply filters when they change
    React.useEffect(() => {
        router.get('/users', { search: debouncedSearch, role: roleFilter }, { preserveState: true, replace: true });
    }, [debouncedSearch, roleFilter]);

    const [deleteDialog, setDeleteDialog] = useState<{
        isOpen: boolean;
        user: User | null;
    }>({ isOpen: false, user: null });

    const openDeleteDialog = (user: User) => {
        setDeleteDialog({ isOpen: true, user });
    };

    const closeDeleteDialog = () => {
        setDeleteDialog({ isOpen: false, user: null });
    };

    const confirmDelete = () => {
        if (deleteDialog.user) {
            router.delete(`/users/${deleteDialog.user.id}`);
            closeDeleteDialog();
        }
    };

    // Define columns for the users table
    const columns: DataTableColumn<User>[] = [
        {
            key: 'name',
            header: 'Name',
            cell: (user) => <span className="font-medium text-foreground">{user.name}</span>,
        },
        {
            key: 'email',
            header: 'Email',
            cell: (user) => <span className="text-muted-foreground">{user.email}</span>,
        },
        {
            key: 'roles',
            header: 'Roles',
            cell: (user) => DataTableCells.badge(user.roles, { variant: 'secondary' }),
        },
    ];

    // Define actions for the users table
    const actions: DataTableAction<User>[] = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: 'View',
            href: (user) => `/users/${user.id}`,
            variant: 'ghost',
        },
        {
            key: 'edit',
            icon: <Edit className="w-4 h-4" />,
            label: 'Edit',
            href: (user) => `/users/${user.id}/edit`,
            variant: 'ghost',
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: openDeleteDialog,
            variant: 'ghost',
            visible: canDelete,
            disabled: (user) => !canDelete(user),
            tooltip: (user) => isAdmin(user) ? 'Admin users cannot be deleted' : null,
        },
    ];

    // Role filter component
    const roleFilterComponent = (
        <Select
            value={roleFilter || undefined}
            onValueChange={(value) => setRoleFilter(value === 'all' ? '' : value)}
        >
            <SelectTrigger className="w-44 h-10 bg-muted/50 border-border hover:bg-muted focus:bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg transition-all duration-200">
                <SelectValue placeholder="Filter by role" />
            </SelectTrigger>
            <SelectContent className="bg-popover border-border shadow-lg rounded-lg">
                <SelectItem value="all" className="hover:bg-muted/50 cursor-pointer">
                    All Roles
                </SelectItem>
                {roles.map((role) => (
                    <SelectItem
                        key={role.id}
                        value={role.name}
                        className="hover:bg-muted/50 cursor-pointer"
                    >
                        {role.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <>
            <Head title="Users" />
            <AppContentWrapper>
                <DataTable
                    title="Users"
                    description="Manage user accounts, roles and permissions"
                    data={users.data}
                    columns={columns}
                    pagination={{
                        current_page: users.current_page,
                        last_page: users.last_page,
                        per_page: users.per_page,
                        total: users.total,
                        links: users.links,
                    }}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Search users..."
                    filters={roleFilterComponent}
                    actions={actions}
                    createHref="/users/create"
                    createLabel="Add User"
                    emptyTitle="No users found"
                    emptyDescription="Get started by creating a new user account."
                />
            </AppContentWrapper>

            <DeleteConfirmDialog
                isOpen={deleteDialog.isOpen}
                onClose={closeDeleteDialog}
                onConfirm={confirmDelete}
                itemName={deleteDialog.user?.name}
                description="This action cannot be undone. The user will be permanently removed from the system."
            />
        </>
    );
}


UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/users',
        },
    ],
};